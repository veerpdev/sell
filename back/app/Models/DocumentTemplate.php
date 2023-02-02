<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'title',
        'type',
    ];

    /**
     * Return DocumentSection
     */
    public function sections()
    {
        return $this->hasMany(DocumentSection::class, 'template_id');
    }

    /**
     * Return Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public static function createTemplate($data)
    {
        $templateObj = self::create($data);

        $templateSections = $data['sections'];

        if (is_array($templateSections)) {
            foreach ($templateSections as $section) {
                $section = (object) $section;

                $sectionObj = new DocumentSection();
                $sectionObj->template_id = $templateObj->id;
                $sectionObj->title = $section->title;
                $sectionObj->free_text_default = $section->free_text_default;
                $sectionObj->is_image = isset($section->is_image)
                    ? $sectionObj->is_image
                    : false;
                $sectionObj->save();
            }
        }

        return self::where('id', $templateObj->id)
            ->with('sections')
            ->first();
    }

    public function update(array $attributes = [], array $options = [])
    {
        parent::update($attributes, $options);

        $templateSections = $attributes['sections'];

        if (is_array($templateSections)) {
            $arrSectionID = [];
            foreach ($templateSections as $section) {
                $this->createOrUpdateSection($section);
            }

            $arrDeletingSectionId = DocumentSection::where(
                'template_id',
                $this->id
            )
                ->whereNotIn('id', $arrSectionID)
                ->pluck('id');

            DocumentSection::whereIn('id', $arrDeletingSectionId)->delete();
        }

        return DocumentTemplate::where('id', $this->id)
            ->with('sections')
            ->first();
    }

    public function delete()
    {
        $arrSectionID = [];
        $arrSections = $this->sections;

        foreach ($arrSections as $section) {
            $arrSectionID[] = $section->id;
        }

        $this->sections()->delete();

        parent::delete();
    }

    protected function createOrUpdateSection($section)
    {
        $section = (object) $section;
        $sectionObj = null;
        if (isset($section->id)) {
            $sectionObj = DocumentSection::where('template_id', $this->id)
                ->where('id', $section->id)
                ->first();
        }

        if ($sectionObj == null) {
            $sectionObj = new DocumentSection();
            $sectionObj->template_id = $this->id;
        }

        $sectionObj->title = $section->title;
        $sectionObj->free_text_default = $section->free_text_default;
        $sectionObj->is_image = isset($section->is_image)
            ? $sectionObj->is_image
            : false;
        $sectionObj->save();
        $arrSectionID[] = $sectionObj->id;
    }
}
