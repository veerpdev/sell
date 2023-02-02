<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\Mailbox;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\MailRequest;

class MailController extends Controller
{
    /**
     * [Mail] - List
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $status = $request->filled('status') ?  $request->status : 'inbox';
        $mailList = null;
        $mailboxTable = (new Mailbox())->getTable();
        $mailTable = (new Mail())->getTable();

        if (in_array($status, ['draft', 'sent'])) {
            $mailList = Mail::where('from_user_id', auth()->user()->id)
                ->where('status', $status)
                ->orderByDesc('sent_at')
                ->get()
                ->toArray();
        } else {
            //TODO this should be refactored to Laravel conventions
            $mailList = Mail::select(
                '*',
                "{$mailTable}.id",
                "{$mailboxTable}.status",
                "{$mailboxTable}.is_starred",
                "{$mailboxTable}.is_read"
            )
                ->rightJoin(
                    $mailboxTable,
                    "{$mailboxTable}.mail_id",
                    '=',
                    "{$mailTable}.id"
                )
                ->orderByDesc('sent_at');

            $mailList->where('user_id', auth()->user()->id);

            $sentMailList = Mail::where('from_user_id', auth()->user()->id);

            if ($status == 'deleted') {
                $mailList->where("{$mailboxTable}.status", $status);

                $sentMailList = $sentMailList
                    ->where('status', $status)
                    ->orderByDesc('sent_at');
            } else {
                $mailList->where("{$mailboxTable}.status", 'inbox');
            }

            if ($status == 'unread') {
                $mailList->where('is_read', false);
            } elseif ($status == 'starred') {
                $mailList->where("{$mailboxTable}.is_starred", true);

                $sentMailList = $sentMailList
                    ->whereNot('status', 'deleted')
                    ->where('is_starred', true)
                    ->orderByDesc('sent_at');
            }

            $mailList = $mailList->get()->toArray();

            if (in_array($status, ['starred', 'deleted'])) {
                $mailIds = [];

                foreach ($mailList as $mail) {
                    $mailIds[] = $mail['id'];
                }

                $sentMailList->whereNotIn('id', $mailIds);
                $sentMailList = $sentMailList->get()->toArray();

                $mailList = array_merge($mailList, $sentMailList);
            }
        }

        $returnMails = [];
        $threadIds = [];

        foreach ($mailList as $mail) {
            if (empty($mail['thread_id']) || (!in_array($mail['thread_id'], $threadIds))) {
                $threadIds[] = $mail['thread_id'];
                $returnMails[] = $mail;
            }
        }

        return response()->json(
            [
                'message' => ucfirst($status) . ' Mail List',
                'data' => $this->withAttachmentLinks($returnMails),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Change attachment json string to Array.
     */
    protected function withAttachmentLinks($mailList)
    {
        $baseUrl = url('/');

        foreach ($mailList as $key => $mail) {
            $attachmentList = json_decode($mail['attachment']);

            $attachmentsWithBaseUrl = [];

            if (!empty($attachmentList)) {
                foreach ($attachmentList as $path) {
                    if (substr($path, 0, 1) == '/') {
                        $attachmentsWithBaseUrl[] = $baseUrl . $path;
                    } else {
                        $attachmentsWithBaseUrl[] = $path;
                    }
                }
            }

            $mailList[$key]['attachment'] = $attachmentsWithBaseUrl;
        }

        return $mailList;
    }

    /**
     * [Mail] - Store
     *
     * @param  \App\Http\Requests\MailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MailRequest $request)
    {
        return response()->json(
            [
                'message' => 'New Mail Created',
                'data' => $this->createMail($request),
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Mail] - Show
     *
     * @param  \App\Models\Mail  $mail
     * @return \Illuminate\Http\Response
     */
    public function show(Mail $mail)
    {
        $availableUserIds = json_decode($mail->to_user_ids);

        $availableUserIds[] = $mail->from_user_id;

        $currentUserId = auth()->user()->id;

        if (!in_array($currentUserId, $availableUserIds)) {
            return response()->json(
                [
                    'message' => 'Only available for sender or receiver',
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $mailbox = $mail->mailbox;

        if (!empty($mailbox)) {
            $mailbox->is_read = true;
            $mailbox->save();
        }

        $repliedMails = [$mail];

        while ($mail->reply_id > 0) {
            $mail = $mail->reply;

            $repliedMails[] = $mail;
        }

        return response()->json(
            [
                'message' => 'Replied Mail List',
                'data' => $this->withAttachmentLinks($repliedMails),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Mail] - Send
     *
     * @param  \App\Http\Requests\MailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function send(MailRequest $request)
    {
        $mail = $this->createMail($request);

        return $this->sendSavedDraft($mail->id);
    }

    /**
     * [Mail] - Create
     *
     * @param  \App\Http\Requests\MailRequest  $request
     * @return $mail
     */
    protected function createMail(MailRequest $request)
    {
        $toUserIds = "[{$request->to_user_ids}]";

        $mail = Mail::create([
            ...$this->filterParams($request),
            'to_user_ids' => $toUserIds,
            'from_user_id' => auth()->user()->id,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        if (empty($mail->thread_id)) {
            $mail->thread_id = $mail->id;
            $mail->save();
        }

        return $mail;
    }

    /**
     * [Mail] - Send Draft Mail
     *
     * @param  \App\Http\Requests\MailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function sendDraft(MailRequest $request)
    {
        $mailId = $request->id;
        $mail = Mail::find($mailId);

        $this->updateMail($request, $mail);

        return $this->sendSavedDraft($mailId);
    }

    /**
     * Send Mail Draft.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    protected function sendSavedDraft($id)
    {
        $mailId = $id;
        $mail = Mail::find($mailId);

        if (auth()->user()->id != $mail->from_user_id) {
            return response()->json(
                [
                    'message' => 'Not Mail Draft owner',
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $toUserIds = json_decode($mail->to_user_ids);

        foreach ($toUserIds as $to_user_id) {
            Mailbox::create([
                'user_id' => $to_user_id,
                'mail_id' => $mail->id,
            ]);
        }

        $mail->status = 'sent';
        $mail->sent_at = date('Y-m-d H:i:s');

        $mail->save();

        return response()->json(
            [
                'message' => 'Mail Sent',
                'data' => $mail,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Mail] - Bookmark
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bookmark(Request $request)
    {
        $mailId = $request->id;
        $mail = Mail::find($mailId);
        $mailbox = $mail->mailbox;
        $return = $mail;
        $userId = auth()->user()->id;

        if ($userId == $mail->from_user_id) {
            $mail->update([
                'is_starred' => $request->is_starred,
            ]);
        }

        if (!empty($mailbox) && $userId == $mailbox->user_id) {
            $mailbox->update([
                'is_starred' => $request->is_starred,
            ]);

            $return = $mailbox;
        }

        return response()->json(
            [
                'message' => 'Mail Bookmarked',
                'data' => $return,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Mail] - Move to Trash
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $mailId = $request->id;
        $mail = Mail::find($mailId);
        $mailbox = $mail->mailbox;
        $return = $mail;
        $userId = auth()->user()->id;

        if ($userId == $mail->from_user_id) {
            $mail->status = 'deleted';
            $mail->save();
        }

        if (!empty($mailbox) && $userId == $mailbox->user_id) {
            $mailbox->status = 'deleted';
            $mailbox->save();

            $return = $mailbox;
        }

        return response()->json(
            [
                'message' => 'Mail Deleted',
                'data' => $return,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Mail] - Restore from Trash
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request)
    {
        $mailId = $request->id;
        $mail = Mail::find($mailId);
        $mailbox = $mail->mailbox;
        $return = $mail;
        $userId = auth()->user()->id;

        if ($userId == $mail->from_user_id) {
            $mail->status = 'sent';
            $mail->save();
        }

        if (!empty($mailbox) && $userId == $mailbox->user_id) {
            $mailbox->status = 'inbox';
            $mailbox->save();

            $return = $mailbox;
        }

        return response()->json(
            [
                'message' => 'Mail Restored',
                'data' => $return,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Mail] - Update
     *
     * @param  \App\Http\Requests\MailRequest  $request
     * @param  \App\Models\Mail  $mail
     * @return \Illuminate\Http\Response
     */
    private function updateMail(MailRequest $request, Mail $mail)
    {
        if (auth()->user()->id != $mail->from_user_id) {
            return response()->json(
                [
                    'message' => 'Not Mail draft creator',
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        if ($mail->status != 'draft') {
            return response()->json(
                [
                    'message' => 'Sent Mail can not be changed',
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $toUserIds = "[{$request->to_user_ids}]";

        $mail->update([
            ...$this->filterParams($request),
            'to_user_ids' => $toUserIds,
            'from_user_id' => auth()->user()->id,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * [Mail] - Update Draft.
     *
     * @param  \App\Http\Requests\MailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function updateDraft(MailRequest $request)
    {
        $mailId = $request->id;
        $mail = Mail::find($mailId);

        return response()->json(
            [
                'message' => 'Mail Draft Updated',
                'data' => $this->updateMail($request, $mail),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Mail] - Destroy
     *
     * @param  \App\Models\Mail  $mail
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mail $mail)
    {
        if ($mail->status != 'draft') {
            return response()->json(
                [
                    'message' => 'Mail destroy forbidden',
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $mail->delete();

        return response()->json(
            [
                'message' => 'Mail Draft Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Filter Request
     *
     * @param  \App\Http\Requests\MailRequest  $request
     * @return Filtered Array
     */
    protected function filterParams(MailRequest $request)
    {
        return [
            ...$request->all(),
            // 'attachment' => $attachment,
            'reply_id' =>
            $request->reply_id == 'null' ? null : $request->reply_id,
            'thread_id' =>
            $request->thread_id == 'null' ? null : $request->thread_id,
        ];
    }
}
