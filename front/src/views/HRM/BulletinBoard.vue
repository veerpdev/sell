<template>
  <section>
    <template v-for="(bulletin, index) in bulletins" :key="index">
      <CardSection>
        <label class="fs-6 fw-bold mb-2 px-2">
          {{ bulletin.title }}
        </label>
        <div class="d-flex flex-wrap fs-6 mb-4 pe-2">
          <InlineSVG icon="user" class="me-1" />
          <label
            class="d-flex align-items-center fs-6 fw-bold"
            style="color: grey"
          >
            {{ bulletin.created_by_name }}
          </label>
          <InlineSVG icon="calender" class="ms-5 me-1" />
          <label
            class="d-flex align-items-center fs-6 fw-bold"
            style="color: grey"
          >
            {{ new Date(bulletin.created_at).toLocaleDateString("en-AU") }}
          </label>
        </div>
        <div class="d-flex flex-wrap">
          <span v-html="bulletin.body"></span>
        </div>
      </CardSection>
    </template>
  </section>
</template>

<script>
import { defineComponent, onMounted, computed, ref } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { HRMActions } from "@/store/enums/StoreHRMEnums";
import { useStore } from "vuex";

export default defineComponent({
  name: "bulletin-board",
  components: {},
  setup() {
    const store = useStore();
    const loading = ref(true);
    const bulletins = computed(() => store.getters.getBulletinList);

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("HRM", ["Bulletin Board"]);
      store.dispatch(HRMActions.BULLETIN.LIST).then(() => {
        loading.value = false;
      });
    });

    return {
      bulletins,
      loading,
    };
  },
});
</script>
