define([
    'jquery',
    'mage/calendar'
], function ($) {
    const config = {
        isInitialized: false,
        appliedInputNames: [],
    }

    const keyCode = {
        tab: 9,
        escape: 27,
        space: 32,
        pageUp: 33,
        pageDown: 34,
        end: 35,
        home: 36,
        leftArrow: 37,
        upArrow: 38,
        rightArrow: 39,
        downArrow: 40
    }

    return {
        /**
         * @param {string} name
         */
        addInputName: function (name) {
            config.appliedInputNames.push(name);
        },

        /**
         * Add keyboard events to datepicker for wcag compatibility
         *
         * @returns {void}
         */
        initialize: function () {
            if (config.isInitialized) {
                return;
            }

            this.attachKeyDownEvents();
            this.attachGlobalEscapeEvent();

            config.isInitialized = true;
        },

        attachKeyDownEvents: function () {
            const originalDoKeydown = $.datepicker._doKeyDown;

            $.extend($.datepicker, {
                _doKeyDown: function (event) {
                    const $eventTarget = $(event.target);
                    if (!$.datepicker._datepickerShowing
                        || !config.appliedInputNames.includes($eventTarget.attr('name'))
                    ) {
                        return originalDoKeydown(event);
                    }

                    const datePicker = $.datepicker._getInst(event.target);
                    const isRTL = datePicker.dpDiv.is('.ui-datepicker-rtl');
                    let shouldPreventDefault = true;

                    datePicker._keyEvent = true;
                    switch (event.which) {
                        // Select first focusable element inside datepicker markup
                        case keyCode.tab:
                            datePicker.dpDiv.find(':focusable').first().focus();
                            break;
                        // Select current date
                        case keyCode.space: {
                            const sel = $("td." + $.datepicker._dayOverClass + ":not(." +
                                $.datepicker._currentClass + ")", datePicker.dpDiv);
                            if (sel[0]) {
                                $.datepicker._selectDay(event.target, datePicker.selectedMonth, datePicker.selectedYear, sel[0]);
                            }

                            const onSelect = $.datepicker._get(datePicker, "onSelect");
                            if (onSelect) {
                                const dateStr = $.datepicker._formatDate(datePicker);

                                // Trigger custom callback
                                onSelect.apply((datePicker.input ? datePicker.input[0] : null), [dateStr, datePicker]);
                            } else {
                                $.datepicker._hideDatepicker();
                            }
                            break;
                        }
                        // Changes the grid of dates to the previous month, or with Shift to the same month in the previous year
                        case keyCode.pageUp:
                            if (event.shiftKey) {
                                $.datepicker._adjustDate(event.target, -$.datepicker._get(datePicker, "stepBigMonths"), "M");
                            } else {
                                originalDoKeydown(event);
                                shouldPreventDefault = false;
                            }
                            break;
                        // Changes the grid of dates to the next month, or with Shift to the same month in the next year
                        case keyCode.pageDown:
                            if (event.shiftKey) {
                                $.datepicker._adjustDate(event.target, +$.datepicker._get(datePicker, "stepBigMonths"), "M");
                            } else {
                                originalDoKeydown(event);
                                shouldPreventDefault = false;
                            }
                            break;
                        // Moves focus to the last day of the current week
                        case keyCode.end: {
                            const date = new Date(datePicker.selectedYear, datePicker.selectedMonth, datePicker.selectedDay);
                            const offset = (date.getDay() < 6 ? 6 - date.getDay() : 7);
                            $.datepicker._adjustDate(event.target, offset, 'D');
                            break;
                        }
                        // Moves focus to the first day of the current week
                        case keyCode.home: {
                            const date = new Date(datePicker.selectedYear, datePicker.selectedMonth, datePicker.selectedDay);
                            const offset = (date.getDay() > 0 ? date.getDay() : 7);
                            $.datepicker._adjustDate(event.target, -offset, 'D');
                            break;
                        }
                        // Moves focus to the previous day
                        case keyCode.leftArrow:
                            $.datepicker._adjustDate(event.target, (isRTL ? +1 : -1), 'D');
                            break;
                        // Moves focus to the same day of the previous week
                        case keyCode.upArrow:
                            $.datepicker._adjustDate(event.target, -7, 'D');
                            break;
                        // Moves focus to the next day
                        case keyCode.rightArrow:
                            $.datepicker._adjustDate(event.target, (isRTL ? -1 : +1), 'D');
                            break;
                        // Moves focus to the same day of the next week
                        case keyCode.downArrow:
                            $.datepicker._adjustDate(event.target, +7, 'D');
                            break;
                        // Call original event handlers
                        default:
                            originalDoKeydown(event);
                            shouldPreventDefault = false;
                            break;
                    }

                    if (shouldPreventDefault) {
                        event.stopPropagation();
                        event.preventDefault();
                    }
                }
            });
        },

        /**
         * Close datepicker if escape key is pressed
         *
         * @returns {void}
         */
        attachGlobalEscapeEvent: function () {
            $(document).on('keydown', function(event) {
                if (event.which !== keyCode.escape || !$.datepicker._datepickerShowing) {
                    return;
                }

                const $lastInput = $($.datepicker._lastInput);

                if (!config.appliedInputNames.includes($lastInput.attr('name'))) {
                    return;
                }

                $($.datepicker._lastInput).focus();
                $.datepicker._hideDatepicker();
            });
        }
    };
});
