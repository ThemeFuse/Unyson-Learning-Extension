fwEvents.one('fw-builder:' + 'quiz-builder' + ':register-items', function (builder) {
	var localized = fw_quiz_builder_item_type_single_choice;

	var ItemView = builder.classes.ItemView.extend({
		template: _.template(
			'<div class="fw-quiz-builder-item-style-default fw-quiz-builder-item-type-single-choice">' +
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
			'<div class="fw-quiz-item-label">' +
			'<div class="fw-quiz-item-question-label">' +
			'<div class="fw-quiz-item-preview-question-wrapper"><label><%- question %></label></div>' +
			'</div>' +
			'</div>' +
			'<div class="fw-quiz-item-label">' +
			'<div class="fw-quiz-item-title-label">' +
			'<div class="fw-quiz-item-preview-title-wrapper"><label><%= firstItem %></label></div>' +
			'</div>' +
			'</div>' +
			'<div class="fw-quiz-items-container closed"><%= hiddenItems %></div>' +
			'</div>'
		),
		events: {
			'click .fw-quiz-item-control-edit': 'openEdit',
			'click .fw-quiz-builder-item-type-single-choice': 'openEdit',
			'click .fw-quiz-item-control-remove': 'removeItem',
			'click .fw-quiz-builder-item-type-single-choice .fw-quiz-item-expand-more': 'expandItems'
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
					this.model.set('options', this.parseValues(values));
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
			var that = this;
			this.defaultRender({
				question: fw.opg('question', this.model.get('options')),
				firstItem: function () {
					var items = that.getItems();

					if (items.length == 0) {
						return '<span class="fw-quiz-item-single-choice-checkbox"></span>'
						+ '<span class="fw-quiz-item-single-choice-text">' + localized.l10n.name + '</span>';
					}

					if (items.length == 1) {
						return '<span class="fw-quiz-item-single-choice-checkbox"></span>'
						+ '<span class="fw-quiz-item-single-choice-text">' + fwQuizBuilder.esc_attr(items[0]) + '</span>';
					}

					if (items.length > 1) {
						return '<span class="fw-quiz-item-single-choice-checkbox"></span>'
						+ '<span class="fw-quiz-item-single-choice-text">' + fwQuizBuilder.esc_attr(items[0])
						+ ' <a href="#" class="fw-quiz-item-expand-more">(' + ( items.length - 1 ) + ' ' + localized.l10n.more_items + ')</a>'
						+ '</span>';
					}

				}(),
				hiddenItems: function () {
					var items = that.getItems();

					if (items.length <= 1) {
						return '';
					}

					var html = '';

					for (var i = 1; i < items.length; i++) {
						html += '<div class="fw-quiz-item-label">' +
						'<div class="fw-quiz-item-title-label">' +
						'<div class="fw-quiz-item-preview-title-wrapper">' +
						'<label>' +
						'<span class="fw-quiz-item-single-choice-checkbox"></span>' +
						'<span class="fw-quiz-item-single-choice-text">' + fwQuizBuilder.esc_attr(items[i]) + '</span>' +
						'</label>' +
						'</div>' +
						'</div>' +
						'</div>';
					}

					return html;
				}(),
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
			return false;
		},
		removeItem: function () {
			this.remove();

			this.model.collection.remove(this.model);
			return false;
		},
		updateDefaultValueFromPreviewInput: function () {
			var values = _.clone(
				// clone to not modify by reference, else model.set() will not trigger the 'change' event
				this.model.get('options')
			);

			this.model.set('options', values);
		},
		expandItems: function (e) {
			e.preventDefault();

			var element = this.$el.find('.fw-quiz-items-container');

			if (element.hasClass('closed')) {
				element.removeClass('closed');
				this.$el.find('.fw-quiz-item-expand-more').text('(' + localized.l10n.close + ')');
			} else {
				element.addClass('closed');
				this.$el.find('.fw-quiz-item-expand-more').text('(' + ( this.getItems().length - 1 ) + ' ' + localized.l10n.more_items + ')');
			}
			return false;

		},
		validateInput: function () {
			var options = this.parseValues(this.model.get('options'));
			var errors = [];

			if (!parseFloat(options.points)) {
				errors.push(localized.l10n.validator.invalid_points);
			}

			if (options['wrong-answers'].length == 0) {
				errors.push(localized.l10n.validator.empty_answers);
			}

			// Process warnings
			if (options['correct-answer'].length == 0) {
				errors.push(localized.l10n.validator.empty_form);
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
		},
		getItems: function () {
			var correct_answer = fw.opg('correct-answer', this.model.get('options')).toString().trim();
			var answers = fw.opg('wrong-answers', this.model.get('options')).toString().trim();

			if (answers.length > 0) {
				answers = answers.toString().split(',');
			}

			if (correct_answer.length == 0 && answers.length == 0) {
				return [];
			}
			if (answers.length == 0) {
				answers = [];
			}
			answers.push(correct_answer);

			if (answers.length > 0) {
				for (var i = 0; i < answers.length; i++) {
					if (answers[i] == '' || answers[i] == undefined) {
						answers.splice(i, 1);
						i--;
					}
				}
			}

			return answers;
		},
		parseValues: function (values) {
			var answers = values['wrong-answers'];

			if (answers.length > 0) {
				for (var i = 0; i < answers.length; i++) {
					if (answers[i] == '' || answers[i] == undefined) {
						answers.splice(i, 1);
						i--;
					}
				}
			}

			values['wrong-answers'] = answers;

			return values;
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