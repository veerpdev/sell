<template>
  <VueCtkDateTimePicker
    :format="format"
    v-model="date_search.date"
    inline
    color="#3E7BA0"
    noKeyboard
    onlyDate
    noButton
    @input="changeDate"
  />

  <div class="d-flex flex-row justify-content-around">
    <button class="btn btn-light-primary btn-sm" @click="updateDate(6)">
      1Y>
    </button>
    <button class="btn btn-light-primary btn-sm" @click="updateDate(5)">
      6M>
    </button>
    <button class="btn btn-light-primary btn-sm" @click="updateDate(4)">
      3M>
    </button>

    <button class="btn btn-light-primary btn-sm" @click="updateDate(3)">
      1M>
    </button>
    <button class="btn btn-light-primary btn-sm" @click="updateDate(2)">
      2W>
    </button>
    <button class="btn btn-light-primary btn-sm" @click="updateDate(1)">
      1W>
    </button>
    <button class="btn btn-primary btn-sm" @click="updateDate(0)">Now</button>
  </div>
</template>

<script lang="ts">
import { defineComponent, reactive, ref, watch } from "vue";
import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
export default defineComponent({
  name: "booing-drawer",
  components: { VueCtkDateTimePicker },
  emits: ["changeDate"],
  props: {
    date_: {
      type: Object,
      required: true,
    },
  },
  setup(props, { emit }) {
    const format = ref("YYYY-MM-DD");
    const date_search = reactive({
      date: new Date(),
    });

    watch(props.date_, () => {
      date_search.date = props.date_.date;
    });

    watch(date_search, () => {
      emit("changeDate", 7, date_search.date);
    });
    const updateDate = (data) => {
      emit("changeDate", data);
    };
    const changeDate = (event) => {
      console.log(event);
    };
    return {
      updateDate,
      format,
      date_search,
      changeDate,
    };
  },
});
</script>
