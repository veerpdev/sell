<template>
  <el-autocomplete
    class="w-100"
    :fetch-suggestions="searchDoctorAddressBook"
    placeholder="Please input"
    :trigger-on-focus="true"
    @select="onSelect"
  >
    <template #default="{ item }">
      <div class="name">
        {{ item.title }}
        {{ item.first_name }} {{ item.last_name }}
      </div>
      <div class="address">{{ item.address }}</div>
    </template>
  </el-autocomplete>
</template>

<script>
import { useStore } from "vuex";
import { defineComponent, computed } from "vue";
export default defineComponent({
  props: {
    onSelect: { required: true },
  },
  setup() {
    const store = useStore();
    const doctorAddressBooks = computed(
      () => store.getters.getDoctorAddressBookList
    );

    let timeout;
    const searchDoctorAddressBook = (term, cb) => {
      const results = term
        ? doctorAddressBooks.value.filter(createFilter(term))
        : doctorAddressBooks.value;

      clearTimeout(timeout);
      timeout = setTimeout(() => {
        cb(results);
      }, 1000);
    };

    const createFilter = (term) => {
      const keyword = term.toString();
      return (doctorAddressBook) => {
        const full_name =
          doctorAddressBook.title +
          " " +
          doctorAddressBook.first_name +
          " " +
          doctorAddressBook.last_name;
        const full_name_pos = full_name
          .toLowerCase()
          .indexOf(keyword.toLowerCase());
        const address_pos = doctorAddressBook.address
          .toLowerCase()
          .indexOf(keyword.toLowerCase());
        return full_name_pos !== -1 || address_pos !== -1;
      };
    };
    return { searchDoctorAddressBook };
  },
});
</script>
