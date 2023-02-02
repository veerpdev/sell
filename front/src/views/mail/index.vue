<template>
  <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div id="kt_content_container">
      <!--begin::Inbox App - Messages -->
      <div class="d-flex flex-column flex-lg-row">
        <!--begin::Sidebar-->
        <div
          class="flex-column flex-lg-row-auto w-100 w-lg-275px mb-10 mb-lg-0"
        >
          <!--begin::Sticky aside-->
          <div
            class="card card-flush mb-0"
            data-kt-sticky="false"
            data-kt-sticky-name="inbox-aside-sticky"
            data-kt-sticky-offset="{default: false, xl: '0px'}"
            data-kt-sticky-width="{lg: '275px'}"
            data-kt-sticky-left="auto"
            data-kt-sticky-top="150px"
            data-kt-sticky-animation="false"
            data-kt-sticky-zindex="95"
          >
            <!--begin::Aside content-->
            <div class="card-body">
              <!--begin::Button-->
              <router-link
                to="/mailbox/compose"
                class="btn btn-primary text-capitalize w-100 mb-10"
              >
                Compose
              </router-link>
              <!--end::Button-->
              <!--begin::Menu-->
              <div
                class="menu menu-column menu-rounded menu-state-bg menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary mb-10"
              >
                <!--begin::Menu item-->
                <div class="menu-item mb-3">
                  <!--begin::Inbox-->
                  <span
                    :class="`menu-link ${
                      emailType.data === 'inbox' ? 'active' : ''
                    }`"
                    @click="switchType('inbox')"
                  >
                    <span class="menu-icon">
                      <!--begin::Svg Icon | path: icons/duotune/communication/com010.svg-->
                      <span class="svg-icon svg-icon-2 me-3">
                        <inline-svg
                          src="media/icons/duotune/communication/com010.svg"
                        />
                      </span>
                      <!--end::Svg Icon-->
                    </span>
                    <span class="menu-title fw-bolder">Inbox</span>
                    <span class="badge badge-light-success"></span>
                  </span>
                  <!--end::Inbox-->
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item mb-3">
                  <!--begin::Starred-->
                  <span
                    :class="`menu-link ${
                      emailType.data === 'starred' ? 'active' : ''
                    }`"
                    @click="switchType('starred')"
                  >
                    <span class="menu-icon">
                      <!--begin::Svg Icon | path: icons/duotune/abstract/abs024.svg-->
                      <span class="svg-icon svg-icon-2 me-3">
                        <inline-svg
                          src="media/icons/duotune/abstract/abs024.svg"
                        />
                      </span>
                      <!--end::Svg Icon-->
                    </span>
                    <span class="menu-title fw-bolder">Starred</span>
                  </span>
                  <!--end::Starred-->
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item mb-3">
                  <!--begin::Draft-->
                  <span
                    :class="`menu-link ${
                      emailType.data === 'draft' ? 'active' : ''
                    }`"
                    @click="switchType('draft')"
                  >
                    <span class="menu-icon">
                      <!--begin::Svg Icon | path: icons/duotune/files/fil024.svg-->
                      <span class="svg-icon svg-icon-2 me-3">
                        <inline-svg
                          src="media/icons/duotune/files/fil024.svg"
                        />
                      </span>
                      <!--end::Svg Icon-->
                    </span>
                    <span class="menu-title fw-bolder">Draft</span>
                  </span>
                  <!--end::Draft-->
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item mb-3">
                  <!--begin::Sent-->
                  <span
                    :class="`menu-link ${
                      emailType.data === 'sent' ? 'active' : ''
                    }`"
                    @click="switchType('sent')"
                  >
                    <span class="menu-icon">
                      <!--begin::Svg Icon | path: icons/duotune/general/gen016.svg-->
                      <span class="svg-icon svg-icon-2 me-3">
                        <inline-svg
                          src="media/icons/duotune/general/gen016.svg"
                        />
                      </span>
                      <!--end::Svg Icon-->
                    </span>
                    <span class="menu-title fw-bolder">Sent</span>
                  </span>
                  <!--end::Sent-->
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item">
                  <!--begin::Trash-->
                  <span
                    :class="`menu-link ${
                      emailType.data === 'deleted' ? 'active' : ''
                    }`"
                    @click="switchType('deleted')"
                  >
                    <span class="menu-icon">
                      <!--begin::Svg Icon | path: icons/duotune/general/gen027.svg-->
                      <span class="svg-icon svg-icon-2 me-3">
                        <inline-svg
                          src="media/icons/duotune/general/gen027.svg"
                        />
                      </span>
                      <!--end::Svg Icon-->
                    </span>
                    <span class="menu-title fw-bolder">Trash</span>
                  </span>
                  <!--end::Trash-->
                </div>
                <!--end::Menu item-->
              </div>
              <!--end::Menu-->
            </div>
            <!--end::Aside content-->
          </div>
          <!--end::Sticky aside-->
        </div>
        <!--end::Sidebar-->
        <!--begin::Content-->
        <div class="flex-lg-row-fluid ms-lg-7 ms-xl-10">
          <!--begin::Card-->
          <router-view :mailType="emailType"></router-view>
          <!--end::Card-->
        </div>
        <!--end::Content-->
      </div>
      <!--end::Inbox App - Messages -->
    </div>
    <!--end::Container-->
  </div>
</template>

<script>
import { defineComponent, reactive } from "vue";
import { useRouter } from "vue-router";

export default defineComponent({
  name: "email-list",

  components: {},

  setup() {
    const emailType = reactive({
      data: "inbox",
    });

    const router = useRouter();

    const switchType = (type) => {
      emailType.data = type;
      router.push({ name: "mailbox-list" });
    };

    return {
      emailType,
      switchType,
    };
  },
});
</script>
