fwEvents.one('fw-builder:' + 'quiz-builder' + ':register-items', function (builder) {
	var localized = fw_quiz_builder_item_type_true_false;

	var ItemView = builder.classes.ItemView.extend({
		template: _.template(
			'<div class="fw-quiz-builder-item-style-default fw-quiz-builder-item-type-true-false">' +
			'<div class="fw-quiz-item-controls fw-row">' +
			'<div class="fw-quiz-item-controls-left fw-col-xs-8">' +
			'<div class="fw-quiz-item-width"></div>' +
			'</div>' +
			'<div class="fw-quiz-item-controls-right fw-col-xs-4 fw-text-right">' +
			'<div class="fw-quiz-item-control-buttons">' +
			'<a class="fw-quiz-item-control-info dashicons dashicons-info" data-hover-tip="<%- info %>" href="#" onclick="return false;" ></a>' +
			'<a class="fw-quiz-item-control-edit dashicons dashicons-welcome-write-blog" data-hover-tip="<%- edit %>" href="#" onclick="return false;" ></a>' +
			'<a class="fw-quiz-item-control-remove dashicons dashicons-no" data-hover-tip="<%- remove %>" href="#" onclick="return false;" ></a>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'<div class="fw-quiz-item-preview">' +
			'<div class="fw-quiz-item-label">' +
			'<div class="fw-quiz-item-preview-label-wrapper"><label data-hover-tip="<%- edit_label %>"><%- question %></label></div>' +
			'<div class="fw-quiz-item-preview-label-edit"><!-- --></div>' +
			'</div>' +
			'</div>' +
			'<div class="fw-quiz-item-label">' +
			'<div class="fw-quiz-item-title-label">' +
			'<div class="fw-quiz-item-preview-title-wrapper"><label><span class="fw-quiz-item-true-false-checkbox"></span><span class="fw-quiz-item-true-false-text"><%- label %></span></label></div>' +
			'</div>' +
			'</div>' +
			'</div>'
		),
		events: {
			'click .fw-quiz-item-control-edit': 'openEdit',
			'click .fw-quiz-builder-item-type-true-false': 'openEdit',
			'click .fw-quiz-item-control-remove': 'removeItem',
			'click .fw-quiz-item-preview .fw-quiz-item-preview-label label': 'openLabelEditor'
		},
		initialize: function () {
			this.defaultInitialize();

			// prepare edit options modal
			{
				this.modal = new fw.OptionsModal({
					title: localized.l10n.item_title,
					options: this.model.modalOptions,
					values: this.model.get('options'),
					size: 'small'
				});

				this.listenTo(this.modal, 'change:values', function (modal, values) {
					this.model.set('options', values);
				});

				this.model.on('change:options', function () {
					this.modal.set(
						'values',
						this.model.get('options')
					);
				}, this);
			}

			this.widthChangerView = new FwBuilderComponents.ItemView.WidthChanger({
				model: this.model,
				view: this
			});
		},
		render: function () {
			this.defaultRender({
				question: fw.opg('question', this.model.get('options')),
				label: localized.l10n.name,
				default_value: fw.opg('default_value', this.model.get('options')),
				edit: localized.l10n.edit,
				info: this.validateInput(),
				remove: localized.l10n.delete,
				edit_label: localized.l10n.edit_label
			});

			if (this.widthChangerView) {
				this.$('.fw-quiz-item-width').append(
					this.widthChangerView.$el
				);
				this.widthChangerView.delegateEvents();
			}
		},
		openEdit: function () {
			this.modal.open();
		},
		removeItem: function () {
			this.remove();
			this.model.collection.remove(this.model);
			return false
		},
		openLabelEditor: function () {
			this.$('.fw-quiz-item-preview-label-wrapper').hide();
		},
		updateDefaultValueFromPreviewInput: function () {
			var values = _.clone(
				// clone to not modify by reference, else model.set() will not trigger the 'change' event
				this.model.get('options')
			);

			this.model.set('options', values);
		},
		validateInput: function () {
			var options = this.model.get('options');
			var errors = [];

			if (!parseFloat(options.points)) {
				errors.push(localized.l10n.validator.invalid_points);
			}

			if (errors.length == 0) {
				this.$el.removeClass('warning');
				return '';
			} else {
				this.$el.addClass('warning');
			}

			var html = '';

			if (errors.length == 1) {
				html = '<p>' + errors[0] + '</p>';
			} else {
				html = '<ul>';

				for (var i = 0; i < errors.length; i++) {
					html += '<li>' + errors[i] + '</li>';
				}

				html += '</ul>';
			}

			return html;
		}
	});

	var Item = builder.classes.Item.extend({
		defaults: function () {
			var defaults = _.clone(localized.defaults);

			defaults.shortcode = fwQuizBuilder.uniqueShortcode(defaults.type + '_');

			return defaults;
		},
		initialize: function () {
			this.defaultInitialize();

			/**
			 * get options from wp_localize_script() variable
			 */
			this.modalOptions = localized.options;

			this.view = new ItemView({
				id: 'fw-builder-item-' + this.cid,
				model: this
			});
		}
	});

	builder.registerItemClass(Item);
});