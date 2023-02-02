<template>
  <!--begin::Stepper-->
  <div class="mx-auto w-100">
    <!--begin::Content-->
    <div class="d-flex flex-row-fluid flex-center bg-white rounded">
      <div class="w-100 py-20 px-9">
        <div class="d-flex flex-row justify-content-between">
          <HeadingText text="Leave Requests" />
          <el-button
            type="primary"
            :icon="Plus"
            plain
            @click="createNewLeaveRequest"
            >New Leave Request
          </el-button>
        </div>
        <el-divider border-style="double" />
        <Datatable
          :table-header="tableHeader"
          :table-data="tableData"
          :loading="loading"
          :rows-per-page="10"
          :enable-items-per-page-dropdown="true"
        >
          <template v-slot:cell-full_name="{ row: item }">
            {{ item.full_name }}
          </template>

          <template v-slot:cell-created_at="{ row: item }">
            {{ moment(item.created_at).format("YYYY-MM-DD") }}
          </template>

          <template v-slot:cell-description="{ row: item }">
            {{ item.description }}
          </template>

          <template v-slot:cell-start_date="{ row: item }">
            <template v-if="!item.full_day && item.start_date === item.end_date"
              >{{ item.start_date }}
              {{ totalHours(item.start_time, item.end_time, item.start_date) }}
            </template>
            <template v-else-if="item.start_date === item.end_date">
              {{ item.start_date }}
            </template>
            <template v-else>
              {{ item.start_date }} to {{ item.end_date }}
            </template>
          </template>

          <template v-slot:cell-leave_type="{ row: item }">
            {{ item.leave_type }}
          </template>

          <template v-slot:cell-status="{ row: item }">
            <el-tag :type="tagClass(item.status)" disable-transitions
              >{{ item.status }}
            </el-tag>
          </template>
          <template v-slot:cell-action="{ row: item }">
            <div class="d-flex justify-content-end">
              <button
                @click="updateApproval(item, true)"
                v-if="item.role_id !== 5 && item.status == 'Pending'"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
              >
                <span class="svg-icon svg-icon-3">
                  <inline-svg src="media/icons/duotune/general/gen026.svg" />
                </span>
              </button>

              <button
                @click="updateApproval(item, false)"
                v-if="item.role_id !== 5 && item.status == 'Pending'"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
              >
                <span class="svg-icon svg-icon-3">
                  <inline-svg src="media/icons/duotune/general/gen042.svg" />
                </span>
              </button>

              <button
                @click="editRequest(item)"
                v-if="item.role_id !== 5 && item.status == 'Pending'"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
              >
                <span class="svg-icon svg-icon-3">
                  <InlineSVG icon="pencil" />
                </span>
              </button>

              <button
                v-if="
                  item.status == 'Pending' && item.user_id === user.profile.id
                "
                @click="confirmDeleteRequest(item.id)"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
              >
                <span class="svg-icon svg-icon-3">
                  <InlineSVG icon="bin" />
                </span>
              </button>
            </div>
          </template>
        </Datatable>
      </div>
    </div>
  </div>
  <LeaveRequestModal
    @sendRequest="processRequest"
    @deleteRequest="deleteRequest"
  />
</template>
<style lang="scss">
.add-schedual {
  font-size: 1.8rem;
  color: #ff9527;
  justify-content: center;
  padding: 10px;
  margin-top: 20px !important;
  margin-bottom: 20px !important;
}

.delete-schedual {
  margin-right: 20px;
  margin-bottom: 10px;
}
</style>
<script lang="ts">
import {
  computed,
  defineComponent,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";
import { Plus } from "@element-plus/icons-vue";
import { mask } from "vue-the-mask";
import LeaveRequestModal from "@/views/HRM/modals/LeaveRequestModal.vue";
import { Modal } from "bootstrap";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import moment from "moment";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import Swal from "sweetalert2";

export default defineComponent({
  name: "create-employee",
  directives: {
    mask,
  },
  components: { LeaveRequestModal, Datatable },
  setup() {
    const store = useStore();
    const loading = ref(false);
    const leaveList = computed(() => store.getters.hrmDataList);
    const user = computed(() => store.getters.currentUser);
    const tableHeader = ref([
      {
        name: "Name",
        key: "full_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Request created date",
        key: "created_at",
        sortable: true,
        searchable: true,
      },
      {
        name: "Description",
        key: "description",
        sortable: true,
        searchable: true,
      },
      {
        name: "Leave Type",
        key: "leave_type",
        sortable: true,
        searchable: true,
      },
      {
        name: "Period",
        key: "start_date",
        sortable: true,
        searchable: true,
      },
      {
        name: "Status",
        key: "status",
        sortable: true,
        searchable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);

    const formData = ref({
      leaveType: "",
      date: [new Date(), new Date()],
      description: "",
      startTime: "08:30",
      endTime: "17:30",
      isFullDay: true,
      start_date: "",
      end_date: "",
      userId: user.value.profile.id,
    });

    const tableData = ref<Array<unknown>>([]);

    onMounted(() => {
      setCurrentPageBreadcrumbs("Employee Availability", ["HRM"]);
      store.dispatch(Actions.CLINICS.LIST);
      store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST);
      store.dispatch(Actions.USER_LIST);
    });

    onBeforeUnmount(() => {
      store.commit(HRMMutations.DATA.SET_LIST, []);
    });

    watch(leaveList, () => {
      tableData.value = leaveList.value.map((data) => data);
    });

    const editRequest = ($event) => {
      if ($event.status !== "Pending") return;
      const formData = {
        id: $event.id,
        status: $event.status,
        userId: $event.user_id,
        fullName: $event.full_name,
        leaveType: $event.leave_type,
        date: [new Date($event.start_date), new Date($event.end_date)],
        description: $event.description,
        startTime: $event.start_time,
        endTime: $event.end_time,
        isFullDay: $event.full_day === 1 ? true : false,
      };
      store.commit(HRMMutations.DATA.SET_SELECT, { ...formData });
      const modal = new Modal(document.getElementById("leave-request-modal"));
      modal.show();
    };

    const tagClass = (data) => {
      if (data == "Pending") return "";
      else if (data == "Approved") return "success";
      else if (data == "Not Approved") return "danger";
    };

    const createNewLeaveRequest = () => {
      store.commit(HRMMutations.DATA.SET_SELECT, { ...formData.value });
      const modal = new Modal(document.getElementById("leave-request-modal"));
      modal.show();
    };

    const processRequest = (data) => {
      data.date[0] = moment(data.date[0]).format("YYYY-MM-DD");
      data.date[1] = moment(data.date[1]).format("YYYY-MM-DD");
      if (data.id) {
        store.dispatch(HRMActions.EMPLOYEE_LEAVE.UPDATE, data).then((e) => {
          store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST);
        });
        return;
      }
      store.dispatch(HRMActions.EMPLOYEE_LEAVE.CREATE, data).then((e) => {
        store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST);
      });
    };

    const confirmDeleteRequest = (data) => {
      Swal.fire({
        title: "Are you sure?",
        text: "You are about to approve and update this leave request",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          deleteRequest(data);
        }
      });
    };

    const deleteRequest = (data) => {
      store.dispatch(HRMActions.EMPLOYEE_LEAVE.DELETE, data).then((e) => {
        store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST, {
          user_id: user.value.id,
        });
      });
    };

    const totalHours = (startTime, endTime, date) => {
      const startDate = moment(date + " " + startTime);
      const endDate = moment(date + " " + endTime);
      let duration = moment.duration(endDate.diff(startDate)).asHours();
      return duration + " Hours";
    };

    const updateApproval = (item, bool) => {
      let buttonColor = "#dd9733";
      let buttonText = "Yes, not approve it!";
      let caption = "reject";
      if (bool) {
        buttonColor = "#36ae45";
        buttonText = "Yes, Approve it!";
        caption = "approve";
      }
      Swal.fire({
        title: "Are you sure?",
        text: "You are about to " + caption + " this leave request",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: buttonColor,
        cancelButtonColor: "#3085d6",
        confirmButtonText: buttonText,
      }).then((result) => {
        if (result.isConfirmed) {
          if (bool) {
            item.status = "Approved";
            processRequest(formDataConverter(item));
          } else {
            item.status = "Not Approved";
            processRequest(formDataConverter(item));
          }
        }
      });
    };

    const formDataConverter = ($event) => {
      const formData = {
        id: $event.id,
        status: $event.status,
        userId: $event.user_id,
        full_name: $event.full_name,
        leaveType: $event.leave_type,
        date: [new Date($event.start_date), new Date($event.end_date)],
        description: $event.description,
        startTime: $event.start_time,
        endTime: $event.end_time,
        isFullDay: $event.full_day === 1 ? true : false,
      };
      return formData;
    };

    return {
      editRequest,
      tagClass,
      Plus,
      createNewLeaveRequest,
      processRequest,
      deleteRequest,
      tableHeader,
      loading,
      leaveList,
      totalHours,
      moment,
      updateApproval,
      confirmDeleteRequest,
      user,
      tableData,
    };
  },
});
</script>
