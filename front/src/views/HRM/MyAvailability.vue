<template>
  <!--begin::Stepper-->
  <div class="mx-auto w-100">
    <!--begin::Content-->
    <div class="d-flex flex-row-fluid flex-center bg-white rounded">
      <div class="w-100 py-4 px-9">
        <HeadingText text="Available Leave Balance" />
        <el-divider border-style="double" />
        <div class="row">
          <div class="w-50">
            <el-card class="box-card w-50">
              <div class="text item">
                Annual Leave:
                <span>40 Hours left</span>
              </div>
            </el-card>
          </div>
          <div class="w-50 d-flex flex-row justify-content-end">
            <el-button
              type="primary"
              :icon="Plus"
              plain
              @click="createNewLeaveRequest"
              >New Leave Request
            </el-button>
          </div>
        </div>
      </div>
    </div>
    <div class="d-flex flex-row-fluid flex-center bg-white rounded">
      <div class="w-100 py-20 px-9">
        <HeadingText text="Leave Requests" />
        <el-divider border-style="double" />
        <el-table
          :data="tableData"
          style="width: 100%"
          :row-class-name="tableRowClassName"
          @row-click="editRequest($event)"
          class="pt-6"
        >
          <el-table-column prop="description" label="Description" />
          <el-table-column
            prop="start_date"
            column-key="start_date"
            sortable
            label="Leave Period"
            width="250"
          >
            <template #default="scope">
              <div>{{ scope.row.start_date }} to {{ scope.row.end_date }}</div>
            </template>
          </el-table-column>
          <el-table-column
            prop="leave_type"
            label="Leave Type"
            width="180"
            :filters="[
              { text: 'Annual Leave', value: 'Annual Leave' },
              { text: 'Sick Leave', value: 'Sick Leave' },
              { text: 'Parental Leave', value: 'Parental Leave' },
              { text: 'Personal Leave', value: 'Personal Leave' },
            ]"
            :filter-method="filterLeaveType"
            filter-placement="bottom-end"
          />
          <el-table-column
            prop="status"
            label="status"
            width="140"
            :filters="[
              { text: 'Approved', value: 'Approved' },
              { text: 'Pending', value: 'Pending' },
              { text: 'Not Approved', value: 'Not Approved' },
            ]"
            :filter-method="filterTag"
            filter-placement="bottom-end"
          >
            <template #default="scope">
              <el-tag :type="tagClass(scope.row.status)" disable-transitions
                >{{ scope.row.status }}
              </el-tag>
            </template>
          </el-table-column>
        </el-table>
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
import { computed, defineComponent, onMounted, ref, watch } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";
import { Plus } from "@element-plus/icons-vue";
import { mask } from "vue-the-mask";
import LeaveRequestModal from "@/views/HRM/modals/LeaveRequestModal.vue";
import { Modal } from "bootstrap";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import moment from "moment";

export default defineComponent({
  name: "create-employee",
  directives: {
    mask,
  },
  components: { LeaveRequestModal },
  setup() {
    const store = useStore();

    const leaveList = computed(() => store.getters.hrmDataList);
    const user = computed(() => store.getters.userProfile);

    const tableData = ref([]);
    const formData = ref({
      leaveType: "",
      userId: user.value.id,
      date: [new Date(), new Date()],
      description: "",
      startTime: "08:30",
      endTime: "17:30",
      isFullDay: true,
      start_date: "",
      end_date: "",
    });
    onMounted(() => {
      setCurrentPageBreadcrumbs("My Availability", ["HRM"]);
      store.dispatch(Actions.CLINICS.LIST);
      store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST, {
        user_id: user.value.id,
      });
    });

    watch(leaveList, () => {
      tableData.value = leaveList.value;
    });

    const filterTag = (value, row) => {
      return row.status === value;
    };

    const editRequest = ($event) => {
      if ($event.status !== "Pending") return;
      const formData = {
        id: $event.id,
        status: $event.status,
        userId: $event.user_id,
        leaveType: $event.leave_type,
        fullName: $event.full_name,
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

    const tableRowClassName = (data) => {
      if (data.row.status === "Pending") {
        return "warning-row";
      }
      return "";
    };

    const tagClass = (data) => {
      if (data == "Pending") return "";
      else if (data == "Approved") return "success";
      else if (data == "Not Approved") return "danger";
    };

    const filterLeaveType = (value, row) => {
      return row.leaveType === value;
    };

    const createNewLeaveRequest = () => {
      store.commit(HRMMutations.DATA.SET_SELECT, { ...formData.value });
      const modal = new Modal(document.getElementById("leave-request-modal"));
      modal.show();
    };

    const processRequest = (data) => {
      data.status = "pending";
      data.date[0] = moment(data.date[0]).format("YYYY-MM-DD");
      data.date[1] = moment(data.date[1]).format("YYYY-MM-DD");
      if (data.id) {
        store.dispatch(HRMActions.EMPLOYEE_LEAVE.UPDATE, data).then((e) => {
          store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST, {
            user_id: user.value.id,
          });
        });
        return;
      }
      store.dispatch(HRMActions.EMPLOYEE_LEAVE.CREATE, data).then((e) => {
        store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST, {
          user_id: user.value.id,
        });
      });
    };

    const deleteRequest = (data) => {
      store.dispatch(HRMActions.EMPLOYEE_LEAVE.DELETE, data).then((e) => {
        store.dispatch(HRMActions.EMPLOYEE_LEAVE.LIST, {
          user_id: user.value.id,
        });
      });
    };

    return {
      tableData,
      filterTag,
      editRequest,
      tableRowClassName,
      tagClass,
      filterLeaveType,
      Plus,
      createNewLeaveRequest,
      processRequest,
      deleteRequest,
    };
  },
});
</script>
<style lang="scss">
.el-table .warning-row {
  cursor: pointer;
  --el-table-tr-bg-color: #fdf6ec;
}

.el-table .success-row {
  --el-table-tr-bg-color: #f0f9eb;
}

.highlight {
  background: unset;
  padding: unset;
}

.text {
  font-size: 16px;
  line-height: 20px;

  span {
    font-weight: 700;
  }
}
</style>
