Ext.ns('ResourceHider');
/**
 * @var ResourceHider
 * @extends Ext.SplitButton
 * @param config
 * @constructor
 * @xtype resourcehider
 */
ResourceHider.Menu = function(config) {
    config = config || {};
    config.record = config.record || {};

    Ext.applyIf(config, {
        text: _('resourcehider')
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

        if (record.show_in_tree) {
            action = 'hide_in_tree';
            menu.push(this._setAction(action));
        } else {
            action = 'show_in_tree';
            menu.push(this._setAction(action));
        }

        if (record.hide_children_in_tree) {
            action = 'show_children_in_tree';
            menu.push(this._setAction(action));
        } else {
            action = 'hide_children_in_tree';
            menu.push(this._setAction(action));
        }

        // The whole menu
        this.setMenu(menu);
    }

    ,setMenu: function(menu) {
        var hasMenu = (this.menu != null);
        this.menu = Ext.menu.MenuMgr.get(menu);
        if (this.rendered && !hasMenu) {
            this.el.child(this.menuClassTarget).addClass('x-btn-with-menu');
            this.menu.on("show", this.onMenuShow, this);
            this.menu.on("hide", this.onMenuHide, this);
        }
    }

    ,_setAction: function(action) {
        return {
            text: _('resourcehider.' + action)
            ,scope: this
            ,handler: function() {
                console.log(action);
            }
        }
    }
});
Ext.reg('babel-translations', ResourceHider.Menu);
