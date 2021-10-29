/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

define([
    'Magento_Ui/js/grid/columns/column',
    'GhostUnicorns_CrtReports/js/js-beautify/beautify.min'
], function (
    Column,
    beautify
) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'GhostUnicorns_CrtReports/ui/grid/cells/text'
        },

        getStatusColor: function (row) {
            if (row.status == 'Success') {
                return '#90EE90';
            }
            if (row.status == 'Error') {
                return '#ff7a7a';
            }
            return '#ffd97a';
        },
    });
});
