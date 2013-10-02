Ext.ns('ResourceHider');

ResourceHider.load = function(id) {
    // Hide the collapse button causing visual glitches
    var tabs = Ext.getCmp('modx-resource-tabs');
    tabs.tools.toggle.hide();

    var content = Ext.getCmp('modx-resource-content');
    var contentAction = 'hide';
    if (content) {
        content[contentAction]();
    }

    // Add the children grid after the title
    var panel = Ext.getCmp('modx-panel-resource');
    panel.insert(1, {
        xtype: 'resourcehider-crc'
        ,resource: id
    });
};

/**
 * @class ResourceHider.CRC
 * @extends MODx.Panel
 * @param {object} config
 * @xtype resourcehider-crc
 */
ResourceHider.CRC = function(config) {
    config = config || {};

    Ext.apply(config, {
        title: 'test'
        ,style: 'margin-top: 10px; margin-bottom: 10px'
        ,autoHeight: true
        ,collapsible: true
        ,animCollapse: false
        ,hideMode: 'offsets'
        ,defaults: {
            border: false
            ,layout: 'anchor'
            ,anchor: '100%'
        }
        ,items: [{
            html: 'Description goes here'
            ,bodyCssClass: 'panel-desc'
        },{
            bodyCssClass: 'main-wrapper'
            ,items: [{
                xtype: 'resourcehider-grid'
                ,resource: config.resource
            }]
        }]
    });
    ResourceHider.CRC.superclass.constructor.call(this, config);
};
Ext.extend(ResourceHider.CRC, MODx.Panel);
Ext.reg('resourcehider-crc', ResourceHider.CRC);

/**
 * @class ResourceHider.Grid
 * @extends MODx.grid.Grid
 * @param {object} config
 * @xtype resourcehider-grid
 */
ResourceHider.Grid = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        url: ResourceHider.config.connector_url
        ,baseParams: {
            action: 'mgr/resource/getList'
            ,type: 'all'
            ,resource: config.resource || false
        }
        ,fields: ['id', 'pagetitle', 'class_key', 'context_key', 'hide_children_in_tree', 'show_in_tree']
        ,paging: true
        ,remoteSort: true
        ,enableHdMenu: false
        ,trackMouseOver: false
        ,header: false
        ,anchor: '100%'
        ,emptyText: _('resourcehider.no_result')
        ,columns: [{
            header: _('id')
            ,dataIndex: 'id'
            ,width: 120
            ,fixed: true
        },{
            header: _('pagetitle')
            ,dataIndex: 'pagetitle'
        }]
        ,tbar: [{
            text: 'Create'
        }, '->', 'Search goes here']
    });
    ResourceHider.Grid.superclass.constructor.call(this, config);
};
Ext.extend(ResourceHider.Grid, MODx.grid.Grid, {
    getMenu: function() {
        var m = [];

        return m;
    }

    /**
     * Sets the given baseParam in the grid's store & reloads the store
     */
    ,setBaseParam: function(combo, param) {
        var store = this.getStore();
        store.setBaseParam(param, combo.getValue());
        store.removeAll();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }

    /**
     * Edit the resource
     */
    ,edit: function(record) {
        var action = MODx.action ? MODx.action['resource/update'] : 'resource/update';
        var classKey = '';
        if (record.class_key != 'modDocument' && record.class_key != 'modResource') {
            classKey = '&class_key=' + record.class_key;
        }
        location.href = '?a=' + action + '&id=' + record.id + classKey;
    }
});
Ext.reg('resourcehider-grid', ResourceHider.Grid);
