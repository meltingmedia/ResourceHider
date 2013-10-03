Ext.ns('ResourceHider');
/**
 * Perform the desired actions to display the children
 *
 * @param {Number} id
 */
ResourceHider.load = function(id) {
    // Configuration & required data
    var tabs = Ext.getCmp('modx-resource-tabs')
        ,panel = Ext.getCmp('modx-panel-resource')
        ,content = Ext.getCmp('modx-resource-content')

        ,target = ResourceHider.config['target']
        ,contentAction = ResourceHider.config['content_action']
        ,insertIdx = ResourceHider.config['insert_idx']
        ,tabSetActive = ResourceHider.config['set_active_tab'];

    // The content to be inserted in a tab
    var tabContent = {
        title: 'Children'
        ,bodyCssClass: 'main-wrapper'
        ,items: [{
            xtype: 'resourcehider-grid'
            ,resource: id
        }]
    };
    // The content to be inserted in the main panel
    var panelContent = {
        xtype: 'resourcehider-crc'
        ,resource: id
    };

    switch (target) {
        case 'tabs':
            // Place the grid at the desired place
            if (insertIdx == 'last') {
                tabs.add(tabContent);
            } else {
                // A numeric index has been given, let's insert
                tabs.insert(insertIdx, tabContent);
            }
            if (tabSetActive) {
                if (insertIdx == 'last') {
                    insertIdx = tabs.items.length - 1;
                }
                // Make the inserted tab as active
                tabs.setActiveTab(insertIdx);
            }
            break;
        case 'panel':
            if (insertIdx === 0) {
                // Prevent inserting the grid before the panel title
                insertIdx = 1;
            }
            if (insertIdx == 1) {
                // Hide the "collapse" toggle causing visual glitches if grid is inserted after the panel title
                tabs.tools.toggle.hide();
            }
            // Place the grid at the desired place
            if (insertIdx == 'last') {
                panel.add(panelContent);
            } else {
                // A numeric index has been given, let's insert
                panel.insert(insertIdx, panelContent);
            }
            break;
    }

    // What to do with the content area : hide, collapse, none (nothing)
    if (content && contentAction != 'none') {
        content[contentAction]();
    }
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
        title: 'Children'
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
