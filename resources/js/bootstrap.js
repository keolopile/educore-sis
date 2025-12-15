// resources/js/bootstrap.js

import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// If you load jQuery + DataTables with <script> tags in the layout,
// this will safely initialise only .datatable tables.
if (window.jQuery && window.jQuery.fn.DataTable) {
    const $ = window.jQuery;

    $(function () {
        $('table.datatable').DataTable();
    });
}
