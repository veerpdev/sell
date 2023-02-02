<template>
  <!--begin::Stepper-->
  <div class="card card-edit-ref-doctor">
    <div class="card-body">
      <!--begin::Form-->
      <el-form
        @keydown="handleFormKeyEvent($event)"
        @keypress="handleFormKeyEvent($event)"
        @keyup="handleFormKeyEvent($event)"
        :model="formData"
        :rules="rules"
        ref="formRef"
      >
        <!--begin::Modal body-->
        <div class="py-10 px-lg-17">
          <!--begin::Scroll-->
          <div class="row">
            <InputWrapper
              class="col-6"
              required
              label="Provider No"
              prop="providerno"
            >
              <el-input
                v-model="formData.provider_no"
                type="text"
                placeholder="Enter the Provider No"
                v-mask="'#######A'"
                @keyup="handleKeyUp($event)"
              />
            </InputWrapper>
          </div>

          <div
            class="row"
            v-if="formInfo.submitButtonName === 'UPDATE' || isVisible"
          >
            <InputWrapper class="col-2" label="Title" prop="title">
              <el-select
                class="w-100"
                v-model="formData.title"
                placeholder="Select Title"
              >
                <el-option
                  v-for="item in titles"
                  :key="item.value"
                  :value="item.value"
                  :label="item.label"
                />
              </el-select>
            </InputWrapper>

            <InputWrapper
              class="col-5"
              required
              label="First Name"
              prop="firstname"
            >
              <el-input
                v-model="formData.first_name"
                type="text"
                placeholder="Enter the first name"
              />
            </InputWrapper>

            <InputWrapper
              class="col-5"
              required
              label="Last Name"
              prop="lastname"
            >
              <el-input
                v-model="formData.last_name"
                type="text"
                placeholder="Enter the last name"
              />
            </InputWrapper>
            <InputWrapper
              class="col-6"
              required
              label="Preferred Communication Method"
              prop="preferred_communication_method"
            >
              <el-select
                class="w-100"
                v-model="formData.preferred_communication_method"
                placeholder="Select Title"
              >
                <el-option value="EMAIL" label="Email" />
                <el-option value="FAX" label="Fax" />
                <el-option value="HEALTHLINK" label="Healthlink" />
              </el-select>
            </InputWrapper>
            <InputWrapper
              class="col-6"
              required
              label="Practice Name"
              prop="practice_name"
            >
              <el-input
                v-model="formData.practice_name"
                type="text"
                placeholder="Enter practice name"
              />
            </InputWrapper>
            <InputWrapper
              class="col-6"
              label="Practice Address"
              prop="practice_address"
            >
              <div class="el-input">
                <GMapAutocomplete
                  :value="formData.practice_address"
                  placeholder="Enter the Address"
                  @place_changed="updateAddress"
                  :options="{
                    componentRestrictions: {
                      country: 'au',
                    },
                  }"
                >
                </GMapAutocomplete>
              </div>
            </InputWrapper>

            <InputWrapper
              class="col-6"
              required
              label="Practice Phone"
              prop="practice_phone"
            >
              <el-input
                v-model="formData.practice_phone"
                type="text"
                v-mask="'0#-####-####'"
                placeholder="Enter phone number"
              />
            </InputWrapper>

            <InputWrapper
              class="col-6"
              label="Practice Fax"
              v-mask="'0#-####-####'"
              prop="practice_fax"
            >
              <el-input
                v-model="formData.practice_fax"
                type="text"
                placeholder="Enter the fax"
              />
            </InputWrapper>

            <InputWrapper
              class="col-6"
              required
              label="Practice Email"
              prop="practice_email"
            >
              <el-input
                v-model="formData.practice_email"
                type="text"
                placeholder="Enter email address"
              />
            </InputWrapper>

            <InputWrapper
              class="col-6"
              label="Health Link"
              prop="healthlink_edi"
            >
              <el-input
                v-model="formData.healthlink_edi"
                type="text"
                placeholder="Enter health link"
              />
            </InputWrapper>
          </div>
        </div>
        <!--end::Scroll-->

        <!--end::Modal body-->

        <!--begin::Modal footer-->
        <div class="modal-footer flex-center">
          <!--begin::Button-->
          <router-link
            type="reset"
            to="/settings/doctor-address-books"
            class="btn btn-light me-3"
          >
            Cancel
          </router-link>
          <!--end::Button-->

          <!--begin::Button-->
          <button
            @click.prevent="submit()"
            :data-kt-indicator="loading ? 'on' : null"
            class="btn btn-lg btn-primary"
            :disabled="formInfo.submitButtonName === 'CREATE' && !isVisible"
          >
            <span v-if="!loading" class="indicator-label">
              {{ formInfo.submitButtonName }}
            </span>
            <span v-if="loading" class="indicator-progress">
              Please wait...
              <span
                class="spinner-border spinner-border-sm align-middle ms-2"
              ></span>
            </span>
          </button>
          <!--end::Button-->
        </div>
        <!--end::Modal footer-->
      </el-form>
      <!--end::Form-->
    </div>
  </div>
</template>
<style lang="scss">
.card-edit-ref-doctor {
  .upload-file {
    .el-form-item__content {
      & > div {
        display: flex;
        gap: 10px;
        & > ul {
          margin-top: -3px;
        }
      }
    }
  }
}
</style>
<script>
import {
  defineComponent,
  onMounted,
  ref,
  reactive,
  watch,
  watchEffect,
  computed,
} from "vue";
import { useStore } from "vuex";
import { useRouter, useRoute } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions } from "@/store/enums/StoreEnums";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import { mask } from "vue-the-mask";
import { validatePhone } from "@/helpers/helpers";
import titles from "@/core/data/titles";
export default defineComponent({
  name: "edit-doctor-address-books",
  directives: {
    mask,
  },
  components: {
    InputWrapper,
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const route = useRoute();
    const formRef = ref(null);
    const isVisible = ref(false);
    const doctorAddressBooks = computed(
      () => store.getters.getDoctorAddressBookList
    );
    const loading = ref(false);

    const formInfo = reactive({
      title: "Create Doctor Address Book",
      submitAction: Actions.DOCTOR_ADDRESS_BOOK.CREATE,
      submitButtonName: "CREATE",
      submittedText: "New Doctor Address Book Created",
    });

    const formData = ref({
      provider_no: "",
      title: "dr",
      first_name: "",
      last_name: "",
      practice_address: "",
      practice_phone: "",
      practice_fax: "",
      practice_email: "",
      practice_name: "",
      healthlink_edi: "",
      preferred_communication_method: "EMAIL",
    });

    const rules = ref({
      provider_no: [
        {
          required: true,
          message: "Provider No cannot be blank",
          trigger: "change",
        },
      ],
      title: [
        {
          required: true,
          message: "Title cannot be blank",
          trigger: "change",
        },
      ],
      first_name: [
        {
          required: true,
          message: "First Name cannot be blank",
          trigger: "change",
        },
      ],
      last_name: [
        {
          required: true,
          message: "Last Name cannot be blank",
          trigger: "change",
        },
      ],

      practice_phone: [
        {
          required: true,
          message: "Phone Number cannot be blank",
          trigger: "change",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
      fax: [
        {
          required: true,
          message: "Fax cannot be blank",
          trigger: "change",
        },
      ],

      practice_email: [
        {
          required: true,
          message: "Email cannot be blank",
          trigger: "change",
        },
        {
          type: "email",
          message: "Please input correct email address",
          trigger: ["blur", "change"],
        },
      ],
      practice_name: [
        {
          required: true,
          message: "Practice Name cannot be blank",
          trigger: "change",
        },
      ],
    });

    watchEffect(() => {
      const id = route.params.id;

      doctorAddressBooks.value.forEach((item) => {
        if (item.id == id) {
          formData.value = item;

          formInfo.title = "Edit Doctor Address Book";
          formInfo.submitAction = Actions.DOCTOR_ADDRESS_BOOK.UPDATE;
          formInfo.submitButtonName = "UPDATE";
          formInfo.submittedText = "Doctor Address Book Updated";
        }
      });

      setCurrentPageBreadcrumbs(formInfo.title, ["Settings"]);
    });

    // const handleChange = (file) => {
    //   formData.value.upload_file = [];
    //   formData.value.upload_file.push(file);
    //   formData.value.upload_file_name = file.name;
    // };

    onMounted(() => {
      store.dispatch(Actions.DOCTOR_ADDRESS_BOOK.LIST);
    });

    const handleFormKeyEvent = (e) => {
      if (e.keyCode === 13) {
        e.preventDefault();
      }
    };

    const handleKeyUp = (e) => {
      if (formData.value.provider_no.length === 8) {
        var isExist = false,
          itemId = -1,
          doctorName = "";
        doctorAddressBooks.value.forEach((item) => {
          if (
            item.provider_no.toLowerCase() ===
            formData.value.provider_no.toLowerCase()
          ) {
            isExist = true;
            itemId = item.id;
            doctorName = item.full_name;
          }
        });
        if (isExist) {
          Swal.fire({
            text: `This provider number is already in your address book for ${doctorName}`,
            buttonsStyling: false,
            confirmButtonText: "VIEW",
            showCancelButton: true,
            cancelButtonText: "CANCEL",
            customClass: {
              confirmButton: "btn btn-primary",
              cancelButton: "btn btn-light-primary",
            },
          }).then((result) => {
            if (result.isConfirmed)
              router.push({ name: "editRefDoctors", params: { id: itemId } });
          });
        } else {
          store
            .dispatch(
              Actions.DOCTOR_ADDRESS_BOOK.FIND_BY_PROVIDER_NO,
              formData.value.provider_no
            )
            .then((data) => {
              if (data) {
                Swal.fire({
                  text: `We have preexisting information for this provider number as ${data.full_name}. Would you like to prefill the form?`,
                  buttonsStyling: false,
                  confirmButtonText: "Yes",
                  showCancelButton: true,
                  cancelButtonText: "No",
                  customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-light-primary",
                  },
                }).then((result) => {
                  if (result.isConfirmed) {
                    isVisible.value = true;
                    formData.value = data;
                  } else {
                    isVisible.value = true;
                    formRef.value.resetFields();
                  }
                });
              } else {
                isVisible.value = true;
              }
            });
        }
      }
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          let Data = {};
          Object.keys(formData.value).forEach((key) => {
            Data[key] = formData.value[key];
          });
          store
            .dispatch(formInfo.submitAction, Data)
            .then(() => {
              store.dispatch(Actions.DOCTOR_ADDRESS_BOOK.LIST);
              Swal.fire({
                text: formInfo.submittedText,
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                router.push({ name: "refDoctors" });
              });
            })
            .finally(() => {
              loading.value = false;
            });
          formRef.value.resetFields();
        }
      });
    };

    const updateAddress = (e) => {
      formData.value.practice_address = e.formatted_address;
    };
    return {
      formData,
      formInfo,
      rules,
      formRef,
      loading,
      submit,
      // handleChange,
      titles,
      updateAddress,
      isVisible,
      handleKeyUp,
      handleFormKeyEvent,
    };
  },
});
</script>
