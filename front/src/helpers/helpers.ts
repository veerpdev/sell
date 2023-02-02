export function validatePhone(rule, value, callback) {
  const phone_regex = /^\(?([0-9]{2})\)?[-]?([0-9]{4})[-]?([0-9]{4})$/;
  if (value.match(phone_regex) === null) {
    callback(new Error("Phone number should be 0#-####-####"));
  } else {
    callback();
  }
}

export function validateAbnAcn(rule, value, callback) {
  const abn_acn_regex = /^([0-9]{9,11})$/;
  if (value === "") {
    callback();
  } else if (value.match(abn_acn_regex) === null) {
    callback(new Error("ABN/ACN should be always 11 or 9 digits long."));
  } else {
    callback();
  }
}

export function validatePass(rule, value, callback) {
  if (value === "") {
    callback(new Error("Please input the password"));
  } else {
    // 20 score
    if (/[a-z]/.test(value) === false) {
      callback(new Error("Please input lowercase character"));
    } else if (/[A-Z]/.test(value) === false) {
      // 20 score
      callback(new Error("Please input uppercase character"));
    } else if (/[0-9]/.test(value) === false) {
      // 20 score
      callback(new Error("Please input digit"));
    } else if (
      /* eslint-disable no-useless-escape */
      /[~`!#$%\^&*+=\-\[\]\\';,/{}|\\":<>\?]/g.test(value) === false
    ) {
      // 20 score
      callback(new Error("Please input symbol character"));
    } else {
      callback();
    }
  }
}

import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { ElMessage } from "element-plus";

export function displayServerError(response, action) {
  let html = "";
  if (!response.data) {
    html =
      "<h1>ERROR </h1><textarea style='height: 160px; width: 100%; padding: 10px; color: grey; font-size: small;' readonly>" +
      response +
      "</textarea>";
  } else {
    html =
      "<h1>ERROR " +
      response.status +
      "</h1><textarea style='height: 160px; width: 100%; padding: 10px; color: grey; font-size: small;' readonly>" +
      response.data.message +
      "</textarea>";
  }
  Swal.fire({
    html: html,
    icon: "error",
    buttonsStyling: false,
    confirmButtonText: "Try again later",
    customClass: {
      confirmButton: "btn btn-warning",
    },
  });
}

export function displaySuccessToast(message) {
  ElMessage.success(message);
}

export function displaySuccessModal(text) {
  Swal.fire({
    text: text,
    icon: "success",
    buttonsStyling: false,
    confirmButtonText: "Ok, got it!",
    customClass: {
      confirmButton: "btn btn-primary",
    },
  });
}
import * as shajs from "sha.js";
import * as qz from "qz-tray";
import IPatient from "@/store/interfaces/IPatient";
import IAppointment from "@/store/interfaces/IAppointment";

export function printPatientLabel(
  patient: IPatient,
  appointment: IAppointment,
  printerName: string
) {
  if (!qz.websocket.isActive()) {
    qz.api.setSha256Type(function (data) {
      return shajs("sha256").update(data).digest("hex");
    });
    qz.api.setPromiseType(function (resolver) {
      return new Promise(resolver);
    });
    qz.websocket
      .connect()
      .catch(function (e) {
        console.error(e);
      })
      .then(() => {
        sendLabelToPrint(patient, appointment, printerName);
      });
  } else {
    sendLabelToPrint(patient, appointment, printerName);
  }
}

function sendLabelToPrint(
  patient: IPatient,
  appointment: IAppointment,
  printerName: string
) {
  qz.printers.find(printerName).then((printer) => {
    const config = qz.configs.create(printer);

    let zplLabelString = "^XA^MMT^PW312^LL0240^LS24";
    zplLabelString += zplLabelField(patient.last_name.toUpperCase(), 0, 0);
    zplLabelString += zplLabelField(patient.first_name, 1, 0);
    zplLabelString += zplLabelField(patient.address.trim(), 2, 0);
    zplLabelString += zplLabelField(
      `${patient.suburb.toUpperCase()}, ${
        patient.postcode
      }, ${patient.state?.toUpperCase()}`,
      3,
      0
    );
    zplLabelString += zplLabelField(
      `${appointment.referral.doctor_address_book.full_name} (${appointment.referral.doctor_address_book.provider_no})`,
      4,
      0
    );
    zplLabelString += zplLabelField(
      `M/C#: ${patient.medicare_details.member_number}-${patient.medicare_details.member_reference_number}`,
      5,
      0
    );
    zplLabelString += zplLabelField(`(H)- (M)-${patient.contact_number}`, 6, 0);

    zplLabelString += zplLabelField(
      `DOB: ${patient.date_of_birth.toUpperCase()}`,
      0,
      140
    );
    zplLabelString += zplLabelField(`#${patient.ur_id.toUpperCase()}`, 1, 210);

    zplLabelString += "^PQ1,0,1,Y^XZ";
    console.log(zplLabelString);
    const zplData = [zplLabelString];
    qz.print(config, zplData);
    function zplLabelField(text: string, yLine: number, xOffset: number) {
      const yLineHeight = 25;
      const yShift = yLine * yLineHeight + 20;
      const xShift = 40 + xOffset;
      const fontSettings = "A0N,20,19";
      return `^FT${xShift},${yShift}^${fontSettings}^FH^FD${text}^FS`;
    }
  });
}
