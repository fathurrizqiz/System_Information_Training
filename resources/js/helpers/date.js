import dayjs from "dayjs";

export function formatDate(date, format = "DD/MM/YYYY") {
    if (!date) return "-";
    return dayjs(date).format(format);
}
