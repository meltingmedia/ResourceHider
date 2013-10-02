Ext.ns('ResourceHider');

/**
 * @class ResourceHider.CMP
 * @extends MODx.Panel
 * @param {object} config
 * @xtype resourcehider-grid
 */
ResourceHider.CMP = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        border: false
        ,baseCls: 'modx-formpanel'
        ,cls: 'container'
        ,items: [{
            html: '<h2>' + _('resourcehider') + '</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype: 'panel'
            ,layout: 'anchor'
            ,items: [{
                html: _('resourcehider.cmp_intro')
                ,border: false
                ,bodyCssClass: 'panel-desc'
            },{
                 xtype: 'resourcehider-grid'
                 ,cls: 'main-wrapper'
                 ,preventRender: true
            }]
        }]
    });
    ResourceHider.CMP.superclass.constructor.call(this, config);
};
Ext.extend(ResourceHider.CMP, MODx.Panel);
Ext.reg('resourcehider-cmp', ResourceHider.CMP);

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
        ,grouping: true
        ,groupBy: 'context_key'
        ,singleText: _('resource')
        ,pluralText: _('resources')
        ,columns: [{
            header: _('context')
            ,dataIndex: 'context_key'
            ,hidden: true
        },{
            header: _('id')
            ,dataIndex: 'id'
            ,width: 150
            ,fixed: true
        },{
            header: _('pagetitle')
            ,dataIndex: 'pagetitle'
        },{
            header: _('resourcehider.hidden_children')
            ,dataIndex: 'hide_children_in_tree'
            ,width: 150
            ,fixed: true
            ,renderer: this.renderBoolean
        }]
        ,tbar: this.setTopBar(config)
    });
    ResourceHider.Grid.superclass.constructor.call(this, config);
};
Ext.extend(ResourceHider.Grid, MODx.grid.Grid, {
    getMenu: function() {
        var m = [];
        if (!this.menu.record.show_in_tree) {
            m.push({
                text: _('resourcehider.show_in_tree')
                ,handler: function() {
                    this._performAction(this.menu.record.id, 'show_in_tree');
                }
            });
        }
        if (this.menu.record.hide_children_in_tree) {
            m.push({
                text: _('resourcehider.show_children_in_tree')
                ,handler: function() {
                    this._performAction(this.menu.record.id, 'show_children_in_tree');
                }
            });
        }
        m.push({
            text: _('resource_edit')
            ,handler: function() {
                this.edit(this.menu.record);
            }
        });

        return m;
    }

    ,setTopBar: function(config) {
        var bar = [];
        if (config.resource) {
            console.log('set CRC top bar');
            bar.push({
               text: 'Create'
            });
        } else {
            console.log('set CMP top bar');
            bar.push('->', _('context'), '-', {
                xtype: 'modx-combo-context'
                ,url: ResourceHider.config.connector_url
                ,value: _('resourcehider.all')
                ,baseParams: {
                    action: 'mgr/context/getList'
                    ,exclude: 'mgr'
                    ,sortBy: 'rank'
                }
                ,listeners: {
                    select: function(combo, record, idx) {
                        this.setBaseParam(combo, 'context_key')
                    }
                    ,scope: this
                }
            }, '-',{
                xtype: 'resourcehider-hiddentypes'
                ,value: 'all'
                ,listeners: {
                    select: function(combo, record, idx) {
                        this.setBaseParam(combo, 'type')
                    }
                    ,scope: this
                }
            },'-');
        }

        return bar;
    }

    /**
     * Renders the boolean value as readable text
     * Adds text-align: right to the column
     */
    ,renderBoolean: function(value, metaData, record, rowIndex, colIndex, store) {
        if (value == 0) {
            value = _('no')
        } else if (value == 1) {
            value = _('yes')
        }
        metaData.attr = 'style="text-align: right"';

        return value;
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
     * Restores the show_in_tree status
     */
    ,_performAction: function(id, action) {
        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'mgr/resource/setAction'
                ,id: id
                ,perform: action
            }
            ,listeners: {
                success: {
                    fn: function(r) {
                        this.refresh();
                        this._refreshTree();
                    }
                    ,scope: this
                }
            }
        });
    }

    /**
     * Refresh the resource tree to reflect the changes
     */
    ,_refreshTree: function() {
        var tree = Ext.getCmp('modx-resource-tree');
        // @todo make sure the tree is visible
        if (tree) {
            tree.refresh();
        }
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


ResourceHider.hiddenTypes = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        store: new Ext.data.SimpleStore({
            fields: ['d', 'v']
            ,data: [
                [_('resourcehider.hidden_type_all'), 'all']
                ,[_('resourcehider.hidden_type_children'), 'children']
                ,[_('resourcehider.hidden_type_hidden'), 'hidden']
            ]
        })
        ,displayField: 'd'
        ,valueField: 'v'
        ,mode: 'local'
        ,name: 'type'
        ,hiddenName: 'type'
        ,triggerAction: 'all'
        ,editable: false
        ,selectOnFocus: false
        ,listWidth: 0
    });
    ResourceHider.hiddenTypes.superclass.constructor.call(this, config);
};
Ext.extend(ResourceHider.hiddenTypes, Ext.form.ComboBox);
Ext.reg('resourcehider-hiddentypes', ResourceHider.hiddenTypes);
