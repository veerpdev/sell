import axios, { AxiosInstance } from "axios";
import { AxiosResponse, AxiosRequestConfig } from "axios";
import BillingTokenService from "./BillingTokenService";
import store from "@/store";
import { Actions } from "@/store/enums/StoreEnums";

/**
 * @description service to call HTTP request via Axios
 */
class BillingApiService {
  /**
   * @description property to share vue instance
   */
  public static axios: AxiosInstance;
  public static clientSecret: string;
  public static clientId: string;

  /**
   * @description initialize vue axios
   */
  public static init() {
    BillingApiService.axios = axios.create({
      baseURL: process.env.VUE_APP_BILLING_API_URL,
    });

    BillingApiService.clientSecret = process.env.VUE_APP_BILLING_CLIENT_SECRET;
    BillingApiService.clientId = process.env.VUE_APP_BILLING_CLIENT_ID;
  }

  /**
   * @description set the default HTTP request headers
   */
  public static setHeader(): Promise<boolean> {
    BillingTokenService.destroyToken();
    return store
      .dispatch(Actions.BILLING_TOKEN)
      .then(() => {
        BillingApiService.axios.defaults.headers.common[
          "Authorization"
        ] = `Bearer ${BillingTokenService.getToken()}`;
        BillingApiService.axios.defaults.headers.common["Accept"] =
          "application/json";
        return true;
      })
      .catch(() => {
        return false;
      });
  }

  /**
   * @description send the GET HTTP request
   * @param resource: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static query(
    resource: string,
    params: AxiosRequestConfig
  ): Promise<AxiosResponse> {
    return BillingApiService.axios.get(resource, params);
  }

  /**
   * @description send the GET HTTP request
   * @param resource: string
   * @param slug: string
   * @returns Promise<AxiosResponse>
   */
  public static get(
    resource: string,
    slug = "" as string,
    params = {} as object
  ): Promise<AxiosResponse> {
    return BillingApiService.axios.get(`${resource}/${slug}`, {
      params: params,
    });
  }

  /**
   * @description set the POST HTTP request
   * @param resource: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static post(
    resource: string,
    params,
    config?
  ): Promise<AxiosResponse> {
    return BillingApiService.axios.post(`${resource}`, params, config);
  }

  /**
   * @description send the UPDATE HTTP request
   * @param resource: string
   * @param slug: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static update(
    resource: string,
    slug: string,
    params
  ): Promise<AxiosResponse> {
    return BillingApiService.axios.put(`${resource}/${slug}`, params);
  }

  /**
   * @description Send the PUT HTTP request
   * @param resource: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static put(resource: string, params): Promise<AxiosResponse> {
    return BillingApiService.axios.put(`${resource}`, params);
  }

  /**
   * @description Send the DELETE HTTP request
   * @param resource: string
   * @returns Promise<AxiosResponse>
   */
  public static delete(resource: string): Promise<AxiosResponse> {
    return BillingApiService.axios.delete(resource);
  }
}

export default BillingApiService;
