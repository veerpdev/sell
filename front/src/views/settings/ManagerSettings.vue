<template>
  <div class="d-flex flex-sm-row gap-3">
    <div class="card col-sm-6 shadow-sm">
      <div class="card-header">
        <h3 class="card-title">Appointment Settings</h3>
      </div>
      <router-link to="/settings/apt-types" class="btn btn-light-primary m-2">
        Appointment Types
      </router-link>
      <router-link
        to="/settings/time-requirements"
        class="btn btn-light-primary m-2"
      >
        Appointment Time Requirements
      </router-link>
      <router-link
        to="/settings/notification-templates"
        class="btn btn-light-primary m-2"
      >
        Notification Settings
      </router-link>
      <router-link
        to="/settings/document-templates"
        class="btn btn-light-primary m-2"
      >
        Patient Document Templates
      </router-link>
    </div>

    <div class="card col-sm-6 shadow-sm">
      <div class="card-header">
        <h3 class="card-title">Pre-Admission Settings</h3>
      </div>
      <router-link
        to="/settings/anesthetic-questions"
        class="btn btn-light-primary m-2"
      >
        Booking Anesthetic Questions
      </router-link>
      <router-link
        to="/settings/pre-admission-questions"
        class="btn btn-light-primary m-2"
      >
        Pre-Admission Questions
      </router-link>
      <router-link
        to="/settings/pre-admission-consent"
        class="btn btn-light-primary m-2"
      >
        Pre-Admission Consent
      </router-link>
    </div>
  </div>
  <div class="d-flex flex-sm-row gap-3 mt-6">
    <div class="card col-sm-6 shadow-sm">
      <div class="card-header">
        <h3 class="card-title">General Settings</h3>
      </div>
      <router-link
        to="/settings/organization"
        class="btn btn-light-primary m-2"
      >
        Organisation Settings
      </router-link>
      <router-link to="/clinics" class="btn btn-light-primary m-2">
        Manage Clinics
      </router-link>
      <router-link
        to="/settings/doctor-address-books"
        class="btn btn-light-primary m-2"
      >
        Doctor Address Books
      </router-link>
      <router-link
        to="/settings/patient-print-label-setting"
        class="btn btn-light-primary m-2"
      >
        Patient Label Printing Settings
      </router-link>
      <router-link
        to="/settings/header-footer-templates"
        class="btn btn-light-primary m-2"
      >
        Header/Footer Template
      </router-link>
    </div>
    <div class="card col-sm-6 shadow-sm">
      <div class="card-header">
        <h3 class="card-title">Billing Settings</h3>
      </div>
      <router-link
        to="/setting/billing-items"
        class="btn btn-light-primary m-2"
      >
        Billing Items
      </router-link>
    </div>
  </div>
</template>
