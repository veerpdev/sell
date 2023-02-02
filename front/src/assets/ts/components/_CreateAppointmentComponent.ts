interface IAlerts {
    alert_level: string;
    is_dismissed: boolean;
}

interface IPatientInfoData {
    first_name: string;
    last_name: string;
    date_of_birth: string;
    email: string;
    address: string;
    contact_number: string;
    appointment_confirm_method: string;
    allergies: string;
    clinical_alerts: string;
    also_known_as: Array<any>;
    is_exist: boolean;
    is_ok: boolean;
    alerts: Array<IAlerts>;
}

interface IAptInfoTypeData {
    appointment_type_id: number | string;
    room_id: number | string;
    note: string;
    send_forms: boolean;
    isNewPatient: boolean;
    time_slot: Array<string>;
}

interface IAptInfoData {
    clinic_name: string;
    clinic_id: number;
    send_forms: boolean;
    date: string;
    arrival_time: string;
    time_slot: Array<string>;
    appointment_type_id: number | null;
    specialist_id: number;
    room_id: number | string;
    note: string;
    patient_id: number | null;
    start_time: string;
    is_wait_listed: number;
}

interface IBillingInfoData {
    charge_type: string;
    claim_sources: Array<unknown>;
    procedure_price: number,
    add_other_account_holder: boolean,
}

interface IOtherInfoData {
    anesthetic_questions: boolean;
    anesthetic_answers: Array<number>;
    doctor_address_book_name: string;
    doctor_address_book_id: number | null;
    referral_duration: string;
    referral_date: string;
    no_referral: boolean;
    no_referral_reason: string;
}

interface ICurSpecialist {
    id: number;
    full_name: string;
}

export {
    IAptInfoData,
    IAptInfoTypeData,
    IAlerts,
    IPatientInfoData,
    IBillingInfoData,
    IOtherInfoData,
    ICurSpecialist,
}
