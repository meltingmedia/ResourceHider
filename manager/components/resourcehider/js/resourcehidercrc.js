Ext.ns('ResourceHider');
/**
 * Perform the desired actions to display the children
 *
 * @param {Number} id The current resource ID
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
        title: _('resourcehider.grid_title')
        //,bodyCssClass: 'main-wrapper'
        ,anchor: '100%'
        ,layout: 'anchor'
        ,defaults: {
            border: false
            ,layout: 'anchor'
            ,anchor: '100%'
            ,autoHeight: true
        }
        ,items: [{
            html: _('resourcehider.grid_desc')
            ,xtype: 'box'
            ,cls: 'panel-desc'
        },{
            xtype: 'container'
            ,cls: 'main-wrapper'
            ,items: [{
                xtype: 'resourcehider-grid'
                ,resource: id
            }]
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
                insertIdx = tabs.items.length;
                tabs.add(tabContent);
            } else {
                // A numeric index has been given, let's insert
                tabs.insert(insertIdx, tabContent);
            }
            if (~~tabSetActive === 1) {
                // Make the inserted tab as active (type casting to integer)
                tabs.setActiveTab(~~insertIdx);
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
 * The panel used in the CRC if not "embed" within the tab panel
 *
 * @class ResourceHider.CRC
 * @extends Ext.Panel
 * @param {object} config
 * @xtype resourcehider-crc
 */
ResourceHider.CRC = function(config) {
    config = config || {};

    Ext.apply(config, {
        title: _('resourcehider.grid_title')
        ,cls: 'container shadowbox resourcehider-crc'
        ,autoHeight: true
        ,collapsible: true
        ,animCollapse: false
        ,hideMode: 'offsets'
        ,defaults: {
            border: false
            ,layout: 'anchor'
            ,anchor: '100%'
            ,autoHeight: true
        }
        ,items: [{
            html: _('resourcehider.grid_desc')
            ,xtype: 'box'
            ,cls: 'panel-desc'
        },{
            xtype: 'container'
            ,cls: 'main-wrapper'
            ,items: [{
                xtype: 'resourcehider-grid'
                ,resource: config.resource
            }]
        }]
    });
    ResourceHider.CRC.superclass.constructor.call(this, config);
};
Ext.extend(ResourceHider.CRC, Ext.Panel);
Ext.reg('resourcehider-crc', ResourceHider.CRC);

/**
 * The grid used in the CRC to display (& CRUD) children
 *
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
            action: 'resource/getList'
            ,resource: config.resource || false
        }
        ,fields: ['id', 'pagetitle', 'class_key', 'published', 'publishedon']
        ,paging: true
        ,remoteSort: true
        ,anchor: '100%'
        ,columns: [{
            header: _('id')
            ,dataIndex: 'id'
            ,width: 80
            ,fixed: true
        },{
            header: _('pagetitle')
            ,dataIndex: 'pagetitle'
        },{
            header: _('published')
            ,renderer: this.rendYesNo
            ,dataIndex: 'published'
            ,width: 120
            ,fixed: true
        }]
        ,viewConfig: {
            forceFit: true
            ,headersDisabled: true
            ,autoFill: true
            ,showPreview: true
            ,scrollOffset: 0
            ,emptyText: config.emptyText || _('ext_emptymsg')
        }
        ,tbar: [{
            text: _('resourcehider.create')
            ,handler: this.create
            ,scope: this
        }, '->', {
            xtype: 'trigger'
            ,emptyText: _('resourcehider.search')
            ,onTriggerClick: function(vent) {
                if (this.getValue() !== '') {
                    this.reset();
                    this.fireEvent('change', this, '');
                }
            }
            ,scope: this
            ,listeners: {
                change: {
                    fn: this.search
                    ,scope: this
                }
                ,render: {
                    fn: function(cmp) {
                        new Ext.KeyMap(cmp.getEl(), {
                            key: Ext.EventObject.ENTER
                            ,fn: function() {
                                this.fireEvent('change', this);
                                this.blur();
                                return true;
                            }
                            ,scope: cmp
                        });
                    }
                    ,scope: this
                }
            }
        }]
    });
    ResourceHider.Grid.superclass.constructor.call(this, config);
};
Ext.extend(ResourceHider.Grid, MODx.grid.Grid, {
    getMenu: function() {
        var m = [];
        m.push({
            text: _('resourcehider.edit')
            ,handler: this.edit
        });
        m.push({
            text: this.menu.record.published ? _('resourcehider.unpublish') : _('resourcehider.publish')
            ,handler: this.togglePublish
        });
        m.push('-', {
            text: _('resourcehider.delete')
            ,handler: this.deleteResource
        });

        return m;
    }

    ,search: function(elem, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = elem.getValue() || nv;
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }

    ,create: function() {
        location.href = '?a=' + this.getMODxAction('resource/create') + '&parent=' + this.config.resource;
    }

    ,togglePublish: function() {
        var me = this;
        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'resource/togglePublish'
                ,id: me.menu.record.id || false
            }
            ,listeners: {
                success: {
                    fn: this.refresh
                    ,scope: me
                }
            }
        });
    }

    ,deleteResource: function() {
        var me = this;
        MODx.msg.confirm({
            title: _('resource_delete')
            ,text: _('resource_delete_confirm')
            ,url: me.url
            ,params: {
                action: 'resource/delete'
                ,id: me.menu.record.id
            }
            ,listeners: {
                success: {
                    fn: this.refresh
                    ,scope: me
                }
            }
        });
    }

    ,getMODxAction: function(action) {
        return MODx.action ? MODx.action[action] : action;
    }

    /**
     * Edit the resource
     */
    ,edit: function() {
        var action = this.getMODxAction('resource/update')
            ,record = this.menu.record
            ,classKey = '';

        if (record.class_key != 'modDocument' && record.class_key != 'modResource') {
            classKey = '&class_key=' + record.class_key;
        }
        location.href = '?a=' + action + '&id=' + record.id + classKey + '&parent=' + this.config.resource;
    }
});
Ext.reg('resourcehider-grid', ResourceHider.Grid);
