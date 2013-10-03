Ext.ns('ResourceHider');

/**
 * Load the button into modAB
 *
 * @param {Object} record The current resource data
 */
ResourceHider.load = function(record) {
    var modAB = Ext.getCmp('modx-action-buttons');
    if (modAB) {
        modAB.insert(0, {
            xtype: 'resourcehider-btn'
            ,record: record
        });
        // Keep the spacing between buttons
        modAB.insert(1, '-');
        modAB.doLayout();
    }
};

/**
 * @class ResourceHider.Menu
 * @extends Ext.SplitButton
 * @param {object} config
 * @xtype resourcehider-btn
 */
ResourceHider.Menu = function(config) {
    config = config || {};
    config.record = config.record || {};

    Ext.applyIf(config, {
        text: _('resourcehider.button')
        ,cls: 'x-btn-text bmenu'
        ,url: ResourceHider.config.connector_url
        ,handler: function() {
            if (this.menu && !this.menu.isVisible() && !this.ignoreNextClick) {
                this.showMenu();
            } else {
                this.hideMenu();
            }
        }
    });
    ResourceHider.Menu.superclass.constructor.call(this, config);
    this.buildMenu();
};

Ext.extend(ResourceHider.Menu, Ext.SplitButton, {
    /**
     * Well, this the the method which builds the split button :)
     */
    buildMenu: function() {
        var record = this.record
            ,menu = [];

        // Resource specific
        if (record.show_in_tree) {
            menu.push(this._setAction('hide_in_tree'));
        } else {
            menu.push(this._setAction('show_in_tree'));
        }
        // Resource's children
        if (record.hide_children_in_tree) {
            menu.push(this._setAction('show_children_in_tree'));
        } else {
            menu.push(this._setAction('hide_children_in_tree'));
        }

        // The whole menu
        this._setMenu(menu);
    }

    /**
     * Renders the whole split button
     *
     * @var {Array} menu
     */
    ,_setMenu: function(menu) {
        var hasMenu = (this.menu != null);
        this.menu = Ext.menu.MenuMgr.get(menu);
        if (this.rendered && !hasMenu) {
            this.el.child(this.menuClassTarget).addClass('x-btn-with-menu');
            this.menu.on('show', this.onMenuShow, this);
            this.menu.on('hide', this.onMenuHide, this);
        }
    }

    /**
     * Generates the appropriate menu entry
     *
     * @var {String} action
     */
    ,_setAction: function(action) {
        return {
            text: _('resourcehider.' + action)
            ,scope: this
            ,handler: function() {
                this._performAction(action);
            }
        }
    }

    /**
     * Updates the resource with the appropriate data
     *
     * @var {String} action
     */
    ,_performAction: function(action) {
        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'resource/setAction'
                ,id: this.record.id
                ,perform: action
            }
            ,listeners: {
                success: {
                    fn: function(r) {
                        this.record = r.object;

                        this.buildMenu();
                        this._refreshTree();

                        // @todo: make this de-activable
                        MODx.msg.status({
                            title: _('resourcehider.success_msg_title')
                            ,message: _('resourcehider.success_msg')
                            ,delay: 1
                        });
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
            // @todo find a way to just reload the appropriate node
            tree.refresh();
        }
    }
});
Ext.reg('resourcehider-btn', ResourceHider.Menu);