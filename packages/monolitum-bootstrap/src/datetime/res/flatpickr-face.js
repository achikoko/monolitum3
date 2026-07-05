"use strict";

window.monolitum_flatpickr = function (
    component_id_str,
    is_only_date_bool,
    show_years_first_bool,
    current_value_str,
    locale_str,
    simple_locale_str
) {

    // Build YYYY-MM-DD from LOCAL parts (see note on why not toISOString)
    function toLocalYMD(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, "0");
        const d = String(date.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    }

    function parseInput(str, withTime) {
        if (!withTime && /^\d{4}-\d{2}-\d{2}$/.test(str)) {
            const [y, m, d] = str.split("-").map(Number);
            return new Date(y, m - 1, d);   // parse date-only as local, not UTC
        }
        return new Date(str);
    }

    let defaultDateTime = null;
    if(current_value_str != null){
        defaultDateTime = new Date(current_value_str);
    }

    flatpickr("#" + component_id_str, {
        enableTime: !is_only_date_bool,
        locale: simple_locale_str,      // still needed so the CALENDAR popup is localized
        defaultDate: defaultDateTime,
        altInput: true,
        altFormat: "DISPLAY",       // sentinel — intercepted below
        dateFormat: "ISO",          // sentinel — intercepted below
        formatDate: (date, format) => {
            if (format === "ISO") {                         // the submitted value
                return !is_only_date_bool ? date.toISOString() : toLocalYMD(date);
            }
            return new Intl.DateTimeFormat(locale_str, {     // shown to user
                dateStyle: "long",
                timeStyle: is_only_date_bool ? undefined : "short"
            }).format(date);
        },
        parseDate: (str) => parseInput(str, !is_only_date_bool),    // needed for defaultDate strings
        disableMobile: "true",
    });

}
