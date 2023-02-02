import { createStore } from "vuex";
import { config } from "vuex-module-decorators";

import AuthModule from "@/store/modules/AuthModule";
import BodyModule from "@/store/modules/BodyModule";
import BreadcrumbsModule from "@/store/modules/BreadcrumbsModule";
import ConfigModule from "@/store/modules/ConfigModule";
import AdminModule from "@/store/modules/AdminModule";
import UserModule from "@/store/modules/UserModule";
import OrganisationModule from "@/store/modules/OrganisationModule";
import ClinicsModule from "@/store/modules/ClinicsModule";
import HealthFundModule from "@/store/modules/HealthFundModule";
import PatientsModule from "@/store/modules/PatientsModule";
import PatientsRecallModule from "@/store/modules/PatientsRecallModule";
import EmployeeModule from "@/store/modules/EmployeeModule";
import BookingModule from "@/store/modules/BookingModule";
import AnesthetistModule from "@/store/modules/AnesthetistModule";
import AppointmentModule from "@/store/modules/AppointmentModule";
import AptTypesModule from "@/store/modules/AptTypesModule";
import AptTimeRequireModule from "@/store/modules/AptTimeRequireModule";
import DocumentTemplateModule from "@/store/modules/DocumentTemplateModule";
import MailsModule from "@/store/modules/MailsModule";
import SpecialistsModule from "@/store/modules/SpecialistsModule";
import NotificationTemplatesModule from "@/store/modules/NotificationTemplatesModule";
import MakePaymentModule from "@/store/modules/MakePaymentModule";
import ProfileModule from "@/store/modules/ProfileModule";
import DoctorAddressBookModule from "@/store/modules/DoctorAddressBookModule";
import ProcedureApprovalsModule from "@/store/modules/ProcedureApprovalsModule";
import DocumentModule from "@/store/modules/DocumentModule";
import HRMModule from "@/store/modules/HRMModule";
import PatientsAlertModule from "@/store/modules/PatientsAlertModule";
import HeaderFooterModule from "@/store/modules/HeaderFooterModule";
import CodingModule from "@/store/modules/CodingModule";
import BulletinModule from "@/store/modules/BulletinModule";
import BillingTokenModule from "@/store/modules/BillingTokenModule";
import BillingValidationModule from "@/store/modules/BillingValidationModule";
import FileModule from "@/store/modules/FileModule";
import MbsModule from "@/store/modules/MbsModule";
import OutgoingModule from "@/store/modules/OutgoingModule";
import ScheduleFeeModule from "@/store/modules/ScheduleFeeModule";
import ScheduleItemModule from "@/store/modules/ScheduleItemModule";
import InvoiceModule from "@/store/modules/InvoiceModule";
import OrgAdminModule from "./modules/OrgAdminModule";
import AutoTextModule from "@/store/modules/AutoTextModule";
import PreAdmissionModule from "@/store/modules/PreAdmissionModule";

config.rawError = true;

const store = createStore({
  modules: {
    AuthModule,
    BodyModule,
    BreadcrumbsModule,
    ConfigModule,
    AdminModule,
    UserModule,
    OrganisationModule,
    ClinicsModule,
    HealthFundModule,
    PatientsModule,
    PatientsRecallModule,
    EmployeeModule,
    BookingModule,
    AnesthetistModule,
    NotificationTemplatesModule,
    AppointmentModule,
    AptTypesModule,
    AptTimeRequireModule,
    DocumentTemplateModule,
    MailsModule,
    SpecialistsModule,
    MakePaymentModule,
    ProfileModule,
    OrgAdminModule,
    DoctorAddressBookModule,
    ProcedureApprovalsModule,
    DocumentModule,
    HRMModule,
    PatientsAlertModule,
    HeaderFooterModule,
    CodingModule,
    BulletinModule,
    BillingTokenModule,
    BillingValidationModule,
    FileModule,
    MbsModule,
    OutgoingModule,
    ScheduleFeeModule,
    ScheduleItemModule,
    InvoiceModule,
    AutoTextModule,
    PreAdmissionModule,
  },
});

export default store;
