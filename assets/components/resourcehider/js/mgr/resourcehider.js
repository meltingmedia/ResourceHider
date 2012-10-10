Ext.ns('ResourceHider');
/**
 * @var ResourceHider
 * @extends Ext.SplitButton
 * @param config
 * @xtype resourcehider
 */
ResourceHider.Menu = function(config) {
    config = config || {};
    config.record = config.record || {};

    Ext.applyIf(config, {
        text: _('resourcehider.button')
        ,cls: 'x-btn-text bmenu'
        ,handler: function() {
            if (this.menu && !this.menu.isVisible() && !this.ignoreNextClick) {
                this.showMenu();
            } else {
                this.hideMenu();
            }
        }
        ,url: ResourceHider.config.connector_url
        ,listeners: {
            setup: {
                fn: this.setup
                ,scope: this
            }
        }
    });
    ResourceHider.Menu.superclass.constructor.call(this, config);
    this.addEvents({setup: true});
    this.fireEvent('setup', config);
};

Ext.extend(ResourceHider.Menu, Ext.SplitButton, {
    setup: function(cmp) {
        this.buildMenu();
    }

    // Build the split button menu
    ,buildMenu: function() {
        var record = this.record;
        var menu = [];

        // Resource specific
        if (record.show_in_tree) {
            menu.push(this._setAction('hide_in_tree'));
        } else {
            menu.push(this._setAction('show_in_tree'));
        }
        // Resource children
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
     */
    ,_setMenu: function(menu) {
        var hasMenu = (this.menu != null);
        this.menu = Ext.menu.MenuMgr.get(menu);
        if (this.rendered && !hasMenu) {
            this.el.child(this.menuClassTarget).addClass('x-btn-with-menu');
            this.menu.on("show", this.onMenuShow, this);
            this.menu.on("hide", this.onMenuHide, this);
        }
    }

    /**
     * Generates the appropriate menu entry
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
     */
    ,_performAction: function(action) {
        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'mgr/resource/setAction'
                ,id: this.record.id
                ,perform: action
            }
            ,listeners: {
                success: {
                    fn: function(r) {
                        this.record = r.object;

                        this.setup();
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
Ext.reg('babel-translations', ResourceHider.Menu);
