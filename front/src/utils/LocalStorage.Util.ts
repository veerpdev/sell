import { StorageKey } from "@/core/enum/storage-key";

export const getLocalStorage = (key: StorageKey) => {
  const data = localStorage.getItem(key);
  return data ? JSON.parse(data) : data;
};

export const setLocalStorage = (key: StorageKey, value: unknown) => {
  const data = value ? JSON.stringify(value) : "";
  localStorage.setItem(key, data);
};
