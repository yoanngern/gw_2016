/*! elementor-pro - v1.0.3 - 13-12-2016 */
(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var EditorModule = function() {
	var self = this;

	this.init = function() {
		Backbone.$( window ).on( 'elementor:init', _.bind( this.onElementorReady, this ) );
	};

	this.onElementorReady = function() {
		self.onElementorInit();

		elementor.on( 'preview:loaded', function() {
			self.onElementorPreviewLoaded();
		} );
	};

	this.init();
};

EditorModule.prototype.onElementorInit = function() {};

EditorModule.prototype.onElementorPreviewLoaded = function() {};

EditorModule.extend = Backbone.View.extend;

module.exports = EditorModule;

},{}],2:[function(require,module,exports){
var ElementorPro = Marionette.Application.extend( {
	config: {},

	modules: {},

	initModules: function() {
		var PanelPostsControl = require( 'modules/panel-posts-control/assets/js/editor' ),
			Forms = require( 'modules/forms/assets/js/editor' ),
			Library = require( 'modules/library/assets/js/editor' ),
			CustomCSS = require( 'modules/custom-css/assets/js/editor' ),
			Slides = require( 'modules/slides/assets/js/editor' ),
			GlobalWidget = require( 'modules/global-widget/assets/js/editor/editor' );

		this.modules = {
			posts: new PanelPostsControl(),
			forms: new Forms(),
			library: new Library(),
			customCSS: new CustomCSS(),
			slides: new Slides(),
			globalWidget: new GlobalWidget()
		};
	},

	ajax: {
		send: function() {
			var args = arguments;

			args[0] = 'pro_' + args[0];

			return elementor.ajax.send.apply( elementor.ajax, args );
		}
	},

	translate: function( stringKey, templateArgs ) {
		return elementor.translate( stringKey, templateArgs, this.config.i18n );
	},

	onStart: function() {
		this.config = ElementorProConfig;

		this.initModules();

		Backbone.$( window ).on( 'elementor:init', this.libraryRemoveGetProButtons );
	},

	libraryRemoveGetProButtons: function() {
		elementor.hooks.addFilter( 'elementor/editor/templateLibrary/remote/actionButton', function() {
			return '#tmpl-elementor-template-library-insert-button';
		});

		elementor.hooks.addFilter( 'elementor/editor/templateLibrary/preview/actionButton', function() {
			return '#tmpl-elementor-template-library-header-preview-insert-button';
		});
	}
} );

window.elementorPro = new ElementorPro();

elementorPro.start();

},{"modules/custom-css/assets/js/editor":3,"modules/forms/assets/js/editor":5,"modules/global-widget/assets/js/editor/editor":7,"modules/library/assets/js/editor":13,"modules/panel-posts-control/assets/js/editor":15,"modules/slides/assets/js/editor":17}],3:[function(require,module,exports){
var EditorModule = require( 'elementor-pro/editor/editor-module' );

module.exports = EditorModule.extend( {
	onElementorInit: function() {
		var CustomCss = require( './editor/custom-css' );
		this.customCss = new CustomCss();
	}
} );

},{"./editor/custom-css":4,"elementor-pro/editor/editor-module":1}],4:[function(require,module,exports){
module.exports = function() {
	var self = this;

	self.init = function() {
		elementor.hooks.addFilter( 'editor/style/styleText', self.addCustomCss );
	};

	self.makeUniqueSelectors = function( selectors, uniqueSelector ) {
		jQuery.each( selectors, function( index ) {
			selectors[ index ] = uniqueSelector + ' ' + this.replace( /selector|[\r\n]/g, '' );
		} );

		return selectors;
	};

	self.addCustomCss = function( css, view ) {
		var model = view.getEditModel(),
			customCss = model.get( 'settings' ).get( 'custom_css' );

		if ( customCss ) {
			var newCss = '',
				uniqueSelector = '#elementor-element-' + view.model.id,
				pattern = /([^{]*)\s*\{\s*([^}]*)\s*}/gi,
				match;

			while ( match = pattern.exec( customCss ) ) {
				var selector = match[ 1 ],
					selectors = selector.split( ',' ),
					rules = match[ 2 ];

				selectors = self.makeUniqueSelectors( selectors, uniqueSelector );

				newCss += selectors.join( ', ' ) + '{' + rules + '}';
			}

			// Fix pseudo selectors like :hove :before and etc.
			var regExp = new RegExp( uniqueSelector + ' \:', 'g' );
			newCss = newCss.replace( regExp, uniqueSelector + '\:' );

			css += newCss;
		}

		return css;
	};

	self.init();
};

},{}],5:[function(require,module,exports){
var EditorModule = require( 'elementor-pro/editor/editor-module' );

module.exports = EditorModule.extend( {
	onElementorPreviewLoaded: function() {
		var ReplyToField = require( './editor/reply-to-field' );
		this.replyToField = new ReplyToField();
	}
} );

},{"./editor/reply-to-field":6,"elementor-pro/editor/editor-module":1}],6:[function(require,module,exports){
module.exports = function() {
	var self = this;

	var getReplayToElement = function() {
		var currentPageView = elementor.getPanelView().getCurrentPageView(),
			replayToControl = currentPageView.collection.findWhere( { name: 'email_reply_to' } ),
			$replayTo = currentPageView.children.findByModelCid( replayToControl.cid ).$el.find( 'select' );
		return $replayTo;
	};

	var updateReplyToOptions = function( settingsModel ) {
		var emailFields = [],
			models = settingsModel.get( 'form_fields' ).models;

		for ( var index in models ) {
			var model = models[ index ];
			if ( 'email' === model.attributes.field_type ) {

				if ( ! model.get( 'field_label' ) ) {
					continue;
				}

				emailFields.push( {
					id: model.get( '_id' ),
					label: model.get( 'field_label' ) + ' ' + ElementorProConfig.i18n.Field
				} );
			}
		}

		var $replayTo = getReplayToElement();

		$replayTo.find( 'option' ).not( 'option:first-child' ).remove();

		for ( var index = 0; index < emailFields.length; index++ ) {
			var item = emailFields[ index ],
				selected = settingsModel.get( 'email_reply_to' ) === item.id,
				option = new Option( item.label, item.id, selected, selected );

			$replayTo.append( option );
		}
	};

	var updateDefaultReplyTo = function( settingsModel ) {
		getReplayToElement().find( 'option:first-child' ).html( settingsModel.get( 'email_from' ) );
	};

	self.onPanelShow = function( panel, model, view ) {
		var settingsModel = model.get( 'settings' );

		settingsModel.on( 'change', self.onFormFieldsChange );

		self.onFormFieldsChange.apply( settingsModel );
	};

	self.onFormFieldsChange = function() {
		updateReplyToOptions( this );
		updateDefaultReplyTo( this );
	};

	self.init = function() {
		elementor.hooks.addAction( 'panel/open_editor/widget/form', self.onPanelShow );
	};

	self.init();
};

},{}],7:[function(require,module,exports){
var EditorModule = require( 'elementor-pro/editor/editor-module' );

module.exports =  EditorModule.extend( {
	globalModels: {},

	panelWidgets: null,

	addGlobalWidget: function( id, args ) {
		args = _.extend( {}, args, {
			categories: [],
			icon: elementor.config.widgets[ args.widgetType ].icon,
			widgetType: args.widgetType,
			custom: {
				templateID: id
			}
		} );

		var globalModel = this.createGlobalModel( id, args );

		return this.panelWidgets.add( globalModel );
	},

	createGlobalModel: function( id, modelArgs ) {
		var globalModel = new elementor.modules.element.Model( modelArgs );

		globalModel.set( 'id', id );

		return this.globalModels[ id ] = globalModel;
	},

	setWidgetType: function() {
		elementor.hooks.addFilter( 'element/view', function( DefaultView, model ) {
			if ( model.get( 'templateID' ) ) {
				return require( './widget-view' );
			}

			return DefaultView;
		} );

		elementor.hooks.addFilter( 'element/model', function( DefaultModel, attrs ) {
			if ( attrs.templateID ) {
				return require( './widget-model' );
			}

			return DefaultModel;
		} );
	},

	registerTemplateType: function() {
		elementor.templates.registerTemplateType( 'widget', {
			showInLibrary: false,
			saveDialog: {
				title: elementorPro.translate( 'global_widget_save_title' ),
				description: elementorPro.translate( 'global_widget_save_description' )
			},
			prepareSavedData: function( data ) {
				data.widgetType = data.data[0].widgetType;

				return data;
			},
			ajaxParams: {
				success: _.bind( this.onWidgetTemplateSaved, this )
			}
		} );
	},

	addSavedWidgetsToPanel: function() {
		var self = this;

		self.panelWidgets = new Backbone.Collection();

		_.each( elementorPro.config.widget_templates, function( templateArgs, id ) {
			self.addGlobalWidget( id, templateArgs );
		} );

		elementor.hooks.addFilter( 'panel/elements/regionViews', function( regionViews ) {
			_.extend( regionViews.global, {
				view: require( './global-templates-view' ),
				options: {
					collection: self.panelWidgets
				}
			} );

			return regionViews;
		} );
	},

	addPanelPage: function() {
		elementor.getPanelView().addPage( 'globalWidget', {
			view: require( './panel-page' )
		} );
	},

	getGlobalModels: function( id ) {
		if ( ! id ) {
			return this.globalModels;
		}

		return this.globalModels[ id ];
	},

	saveTemplates: function() {
		if ( ! Object.keys( this.globalModels ).length ) {
			return;
		}

		var templatesData = [];

		_.each( this.globalModels, function( templateModel, id ) {
			if ( 'loaded' !== templateModel.get( 'settingsLoadedStatus' ) ) {
				return;
			}

			var data = {
				data: JSON.stringify( [ templateModel ] ),
				source: 'local',
				type: 'widget',
				id: id
			};

			templatesData.push( data );
		} );

		elementor.ajax.send( 'update_templates', {
			data: {
				templates: templatesData
			}
		} );
	},

	setSaveButton: function() {
		elementor.getPanelView().footer.currentView.ui.buttonSave.on( 'click', _.bind( this.saveTemplates, this ) );
	},

	requestGlobalModelSettings: function( globalModel, callback ) {
		elementor.templates.requestTemplateContent( 'local', globalModel.get( 'id' ), {
			success: function( data ) {
				globalModel.set( 'settingsLoadedStatus', 'loaded' ).trigger( 'settings:loaded' );

				var settings = data[0].settings,
					settingsModel = globalModel.get( 'settings' );

				settingsModel.handleRepeaterData( settings );

				settingsModel.set( settings );

				if ( callback ) {
					callback( globalModel );
				}
			}
		} );
	},

	onElementorInit: function() {
		this.setWidgetType();
		this.registerTemplateType();
		this.addSavedWidgetsToPanel();
	},

	onElementorPreviewLoaded: function() {
		this.addPanelPage();
		this.setSaveButton();
	},

	onWidgetTemplateSaved: function( data ) {
		var widgetModel = elementor.templates.getLayout().modalContent.currentView.model,
			widgetModelIndex = widgetModel.collection.indexOf( widgetModel );

		elementor.templates.closeModal();

		data.elType = data.type;
		data.settings = widgetModel.get( 'settings' ).attributes;

		var globalModel = this.addGlobalWidget( data.template_id, data ),
			globalModelAttributes = globalModel.attributes;

		widgetModel.collection.add( {
			id: elementor.helpers.getUniqueID(),
			elType: globalModelAttributes.type,
			templateID: globalModelAttributes.template_id,
			widgetType: 'global'
		}, { at: widgetModelIndex }, true );

		widgetModel.destroy();

		var panel = elementor.getPanelView();

		panel.setPage( 'elements' );

		panel.getCurrentPageView().activateTab( 'global' );
	}
} );

},{"./global-templates-view":8,"./panel-page":10,"./widget-model":11,"./widget-view":12,"elementor-pro/editor/editor-module":1}],8:[function(require,module,exports){
module.exports = elementor.modules.templateLibrary.ElementsCollectionView.extend( {
	id: 'elementor-global-templates',

	getEmptyView: function() {
		if ( this.collection.length ) {
			return null;
		}

		return require( './no-templates' );
	},

	onFilterEmpty: function() {}
} );

},{"./no-templates":9}],9:[function(require,module,exports){
module.exports = Marionette.ItemView.extend( {
	template: '#tmpl-elementor-panel-global-widget-no-templates',

	id: 'elementor-panel-global-widget-no-templates',

	className: 'elementor-panel-nerd-box',

	initialize: function() {
		elementor.getPanelView().getCurrentPageView().search.reset();
	},

	onDestroy: function() {
		elementor.getPanelView().getCurrentPageView().showView( 'search' );
	}
} );

},{}],10:[function(require,module,exports){

module.exports = Marionette.ItemView.extend( {
	id: 'elementor-panel-global-widget',

	template: '#tmpl-elementor-panel-global-widget',

	ui: {
		editButton: '#elementor-global-widget-locked-edit .elementor-button',
		unlinkButton: '#elementor-global-widget-locked-unlink .elementor-button',
		loading: '#elementor-global-widget-loading'
	},

	events: {
		'click @ui.editButton': 'onEditButtonClick',
		'click @ui.unlinkButton': 'onUnlinkButtonClick'
	},

	initialize: function() {
		this.initUnlinkDialog();
	},

	buildUnlinkDialog: function() {
		var self = this;

		return elementor.dialogsManager.createWidget( 'confirm', {
			id: 'elementor-global-widget-unlink-dialog',
			headerMessage: elementorPro.translate( 'unlink_widget' ),
			message: elementorPro.translate( 'dialog_confirm_unlink' ),
			position: {
				my: 'center center',
				at: 'center center'
			},
			strings: {
				confirm: elementorPro.translate( 'unlink' ),
				cancel: elementorPro.translate( 'cancel' )
			},
			onConfirm: function() {
				self.getOption( 'editedView' ).unlink();
			}
		} );
	},

	initUnlinkDialog: function() {
		var dialog;

		this.getUnlinkDialog = function() {
			if ( ! dialog ) {
				dialog = this.buildUnlinkDialog();
			}

			return dialog;
		};
	},

	editGlobalModel: function() {
		var editedView = this.getOption( 'editedView' );

		elementor.getPanelView().openEditor( editedView.getEditModel(), editedView );
	},

	onEditButtonClick: function() {
		var self = this,
			editedView = self.getOption( 'editedView' ),
			editedModel = editedView.getEditModel();

		if ( 'loaded' === editedModel.get( 'settingsLoadedStatus' ) ) {
			self.editGlobalModel();

			return;
		}

		self.ui.loading.removeClass( 'elementor-hidden' );

		elementorPro.modules.globalWidget.requestGlobalModelSettings( editedModel, function() {
			self.ui.loading.addClass( 'elementor-hidden' );

			self.editGlobalModel();
		} );
	},

	onUnlinkButtonClick: function() {
		this.getUnlinkDialog().show();
	}
} );

},{}],11:[function(require,module,exports){
module.exports = elementor.modules.element.Model.extend( {
	initialize: function() {
		this.set( { widgetType: 'global' }, { silent: true } );

		elementor.modules.element.Model.prototype.initialize.apply( this, arguments );
	},

	initSettings: function() {},

	initEditSettings: function() {},

	onDestroy: function() {
		var panel = elementor.getPanelView(),
			currentPageName = panel.getCurrentPageName();

		if ( -1 !== [ 'editor', 'globalWidget' ].indexOf( currentPageName ) ) {
			panel.setPage( 'elements' );
		}
	}
} );

},{}],12:[function(require,module,exports){
var WidgetView = elementor.modules.WidgetView,
	GlobalWidgetView;

GlobalWidgetView = WidgetView.extend( {

	globalModel: null,

	className: function() {
		return WidgetView.prototype.className.apply( this, arguments ) + ' elementor-global-widget elementor-global-' + this.model.get( 'templateID' );
	},

	initialize: function() {
		var self = this,
			templateID = self.model.get( 'templateID' );

		self.globalModel = elementorPro.modules.globalWidget.getGlobalModels( templateID );

		var globalSettingsLoadedStatus = self.globalModel.get( 'settingsLoadedStatus' );

		if ( ! globalSettingsLoadedStatus ) {
			self.globalModel.set( 'settingsLoadedStatus', 'pending' );

			elementorPro.modules.globalWidget.requestGlobalModelSettings( self.globalModel );
		}

		if ( 'loaded' !== globalSettingsLoadedStatus ) {
			self.$el.addClass( 'elementor-loading' );
		}

		self.globalModel.on( 'settings:loaded', function() {
			self.$el.removeClass( 'elementor-loading' );

			self.render();
		} );

		WidgetView.prototype.initialize.apply( self, arguments );
	},

	getEditModel: function() {
		return this.globalModel;
	},

	getHTMLContent: function( html ) {
		if ( 'loaded' === this.globalModel.get( 'settingsLoadedStatus' ) ) {
			return WidgetView.prototype.getHTMLContent.call( this, html );
		}

		return '';
	},

	serializeModel: function() {
		return this.globalModel.toJSON.apply( this.globalModel, _.rest( arguments ) );
	},

	edit: function() {
		elementor.getPanelView().setPage( 'globalWidget', 'Global Editing', { editedView: this } );
	},

	unlink: function() {
		var newModel = this.model.clone();

		newModel.setHtmlCache();

		newModel.set( {
			templateID: null,
			widgetType: this.globalModel.get( 'widgetType' ),
			settings: this.globalModel.get( 'settings' ).clone(),
			editSettings: this.globalModel.get( 'editSettings' ).clone()
		} );

		this._parent.addChildModel( newModel, { at: this.model.collection.indexOf( this.model ) } );

		var newWidget = this._parent.children.findByModelCid( newModel.cid );

		this.model.destroy();

		newWidget.edit();
	}
} );

module.exports = GlobalWidgetView;

},{}],13:[function(require,module,exports){
var EditorModule = require( 'elementor-pro/editor/editor-module' );

module.exports = EditorModule.extend( {
	onElementorPreviewLoaded: function() {
		var EditButton = require( './editor/edit-button' );
		this.editButton = new EditButton();
	}
} );
},{"./editor/edit-button":14,"elementor-pro/editor/editor-module":1}],14:[function(require,module,exports){
module.exports = function() {
	var self = this;

	self.onPanelShow = function(  panel ) {
		var templateIdControl = panel.content.currentView.collection.findWhere( { name: 'template_id' } );

		if ( ! templateIdControl ) {
			return; // No templates
		}
		var templateIdInput = panel.content.currentView.children.findByModelCid( templateIdControl.cid );

		templateIdInput.on( 'input:change', self.onTemplateIdChange ).trigger( 'input:change' );
	};

	self.onTemplateIdChange = function() {
		var templateID = this.options.elementSettingsModel.attributes.template_id,
			type = this.options.model.attributes.types[ templateID ],
			$editButton = this.$el.find( '.elementor-edit-template' );

		if ( '0' === templateID || ! templateID || 'widget' === type ) { // '0' = first option, 'widget' is editable only from Elementor page
			if ( $editButton.length ) {
				$editButton.remove();
			}

			return;
		}

		var editUrl = ElementorConfig.home_url + '?p=' + templateID + '&elementor';

		if ( $editButton.length ) {
			$editButton.prop( 'href', editUrl );
		} else {
			$editButton = jQuery( '<a />', {
				target: '_blank',
				class: 'elementor-button elementor-button-default elementor-edit-template',
				href: editUrl,
				html: '<i class="fa fa-pencil" /> ' + ElementorProConfig.i18n.edit_template
		} );

			this.$el.find( '.elementor-control-input-wrapper' ).after( $editButton );
		}
	};

	self.init = function() {
		elementor.hooks.addAction( 'panel/open_editor/widget/template', self.onPanelShow );
	};

	self.init();
};

},{}],15:[function(require,module,exports){
var EditorModule = require( 'elementor-pro/editor/editor-module' );

module.exports = EditorModule.extend( {
	onElementorPreviewLoaded: function() {
		var PanelPostsControl = require( './editor/panel-posts-control' );
		this.panelPostsControl = new PanelPostsControl();
	}
} );

},{"./editor/panel-posts-control":16,"elementor-pro/editor/editor-module":1}],16:[function(require,module,exports){
module.exports = function() {
	var self = this;

	self.onPanelShow = function(  panel ) {
		var filters = panel.getCurrentPageView().children.filter( function( view ) {
			return ( ! _.isEmpty( view.model.get( 'filter_type' ) ) );
		} );

		if ( ! filters.length ) {
			return;
		}

		var controlsWithValues = {};

		_.each( filters, function( view ) {
			self.setInputAjaxSettings( view );

			var value = view.getControlValue();

			if ( value ) {
				controlsWithValues[ view.model.cid ] = {
					filter_type: view.model.get( 'filter_type' ),
					object_type: view.model.get( 'object_type' ),
					value: value
				};
			}
		} );

		if ( ! _.isEmpty( controlsWithValues ) ) {
			self.getControlsValues( controlsWithValues );
		}
	};

	self.getControlsValues = function( controlsWithValues ) {
		var request = elementorPro.ajax.send( 'panel_posts_control_filters_values', {
			data: {
				views: controlsWithValues
			}
		});

		request.then( function( data ) {
			var posts = data.data;

			_.each( posts, function( post, cid ) {
				var view = elementor.getPanelView().getCurrentPageView().children.findByModelCid( cid );

				view.model.set( 'options', post );

				view.render();

				self.setInputAjaxSettings( view );
			} );
		});
	};

	self.setInputAjaxSettings = function( view ) {
		view.$el.find( 'select' ).select2({
			ajax: {
				transport: function( params, success, failure ) {

					var data = {
							q: params.data.q,
							filter_type: view.model.get( 'filter_type' ),
							object_type: view.model.get( 'object_type' )
						};

					return elementorPro.ajax.send( 'panel_posts_control_filter_autocomplete', {
						data: data,
						success: success,
						error: failure
					} );
				},
				data: function( params ) {
					return {
						q: params.term,
						page: params.page
					};
				},
				cache: true
			},
			escapeMarkup: function( markup ) {
				return markup;
			},
			minimumInputLength: 2
		} );
	};

	self.init = function() {
		elementor.hooks.addAction( 'panel/open_editor/widget', self.onPanelShow );
	};

	self.init();
};

},{}],17:[function(require,module,exports){
var EditorModule = require( 'elementor-pro/editor/editor-module' );

module.exports = EditorModule.extend( {
	onElementorPreviewLoaded: function() {
		var StopSlider = require( './editor/stop-slider' );
		this.stopSlider = new StopSlider();
	}
} );

},{"./editor/stop-slider":18,"elementor-pro/editor/editor-module":1}],18:[function(require,module,exports){
module.exports = function() {
	var self = this;

	self.onPanelShow = function( panel, model, view ) {
		var $slider = view.$el.find( '.elementor-slides' );

		if ( $slider.length ) {
			$slider.slick( 'slickPause' );

			$slider.on( 'afterChange', function() {
				$slider.slick( 'slickPause' );
			} );
		}
	};

	self.init = function() {
		elementor.hooks.addAction( 'panel/open_editor/widget/slides', self.onPanelShow );
	};

	self.init();
};

},{}]},{},[2])
//# sourceMappingURL=editor.js.map
