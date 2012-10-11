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
        }
        ,fields: ['id', 'pagetitle']
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
            ,width: 150
            ,fixed: true
        },{
            header: _('pagetitle')
            ,dataIndex: 'pagetitle'
        }]
    });
    ResourceHider.Grid.superclass.constructor.call(this, config)
};
Ext.extend(ResourceHider.Grid, MODx.grid.Grid, {
    getMenu: function() {
        var m = [];
        m.push({
            text: _('resourcehider.show_in_tree')
            ,handler: function() {
                this._performAction(this.menu.record.id);
            }
        });

        return m;
    }

    /**
     * Restores the show_in_tree status
     */
    ,_performAction: function(id) {
        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'mgr/resource/setAction'
                ,id: id
                ,perform: 'show_in_tree'
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
});
Ext.reg('resourcehider-grid', ResourceHider.Grid);
