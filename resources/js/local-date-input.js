import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/esm/l10n/id.js';

function normalizeWeekdays(days) {
    if (! Array.isArray(days)) return [];

    return [...new Set(days.map((day) => Number(day)).filter((day) => day >= 1 && day <= 7))];
}

function isoWeekday(date) {
    const day = date.getDay();

    return day === 0 ? 7 : day;
}

function dateToIso(date) {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');

    return `${yyyy}-${mm}-${dd}`;
}

function displayToDate(value, mode) {
    const source = String(value || '');
    const match = mode === 'datetime'
        ? source.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?$/)
        : source.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

    if (! match) return null;

    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    const hour = Number(match[4] || 0);
    const minute = Number(match[5] || 0);
    const parsed = new Date(year, month - 1, day, hour, minute);

    if (
        parsed.getFullYear() !== year
        || parsed.getMonth() + 1 !== month
        || parsed.getDate() !== day
        || hour > 23
        || minute > 59
    ) {
        return null;
    }

    return parsed;
}

function isoToDisplay(value, mode) {
    const source = String(value || '');
    const match = mode === 'datetime'
        ? source.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/)
        : source.match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (! match) return '';

    const date = `${match[3]}/${match[2]}/${match[1]}`;

    return mode === 'datetime' ? `${date} ${match[4]}:${match[5]}` : date;
}

function isoToDate(value) {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (! match) return null;

    const parsed = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));

    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function createLocalDateInput(config = {}) {
    return {
        displayValue: config.displayValue || '',
        isoValue: config.isoValue || '',
        mode: config.mode || 'date',
        minDate: config.minDate || null,
        maxDate: config.maxDate || null,
        picker: config.picker || 'native',
        allowedWeekdays: normalizeWeekdays(config.allowedWeekdays || []),
        isDisabled: Boolean(config.disabled || false),
        isFormatting: false,
        flatpickrInstance: null,

        init() {
            this.$el.__localDateInput = this;
            this.displayValue = this.fromIso(this.isoValue) || this.displayValue;

            if (this.usesFlatpickr()) {
                this.initFlatpickr();
            }

            this.$watch('isoValue', (value) => {
                if (this.isFormatting) return;

                this.displayValue = this.fromIso(value);
                this.syncFlatpickrDate(value);
            });
        },

        destroy() {
            this.flatpickrInstance?.destroy();
            delete this.$el.__localDateInput;
        },

        usesFlatpickr() {
            return this.picker === 'flatpickr' && this.mode === 'date';
        },

        initFlatpickr() {
            const input = this.$refs.displayInput;
            if (! input) return;

            this.flatpickrInstance = flatpickr(input, {
                appendTo: document.body,
                allowInput: true,
                dateFormat: 'd/m/Y',
                disableMobile: true,
                locale: Indonesian,
                minDate: this.minDate || undefined,
                maxDate: this.maxDate || undefined,
                clickOpens: ! this.isDisabled,
                onChange: (selectedDates) => {
                    const selected = selectedDates[0] || null;
                    if (! selected || ! this.isAllowedDateObject(selected)) {
                        this.setIsoValue('');
                        return;
                    }

                    this.setIsoValue(dateToIso(selected));
                },
                disable: [
                    (date) => ! this.isAllowedDateObject(date),
                ],
            });

            this.syncFlatpickrDate(this.isoValue);
        },

        setDisabled(disabled) {
            this.isDisabled = Boolean(disabled);
            this.flatpickrInstance?.set('clickOpens', ! this.isDisabled);

            if (this.isDisabled) {
                this.flatpickrInstance?.close();
            }
        },

        setAllowedWeekdays(days) {
            this.allowedWeekdays = normalizeWeekdays(days);
            this.flatpickrInstance?.set('disable', [(date) => ! this.isAllowedDateObject(date)]);

            if (this.isoValue && ! this.isAllowedIso(this.isoValue)) {
                this.setIsoValue('');
            }
        },

        formatInput() {
            const digits = this.displayValue.replace(/\D/g, '').slice(0, this.mode === 'datetime' ? 12 : 8);
            const date = [digits.slice(0, 2), digits.slice(2, 4), digits.slice(4, 8)].filter(Boolean).join('/');
            const time = this.mode === 'datetime' && digits.length > 8 ? ` ${digits.slice(8, 10)}${digits.length > 10 ? ':' + digits.slice(10, 12) : ''}` : '';

            this.isFormatting = true;
            this.displayValue = date + time;
            this.isoValue = this.toIso(this.displayValue) || '';
            this.emitModelValue();

            this.$nextTick(() => {
                this.isFormatting = false;
            });
        },

        commitTypedInput() {
            const nextIsoValue = this.toIso(this.displayValue) || '';
            this.setIsoValue(nextIsoValue);
        },

        syncFromPicker(event) {
            this.setIsoValue(event.target.value || '');
        },

        setIsoValue(value) {
            this.isFormatting = true;
            this.isoValue = value || '';
            this.displayValue = this.fromIso(this.isoValue);
            this.syncFlatpickrDate(this.isoValue);
            this.isFormatting = false;
            this.emitModelValue();
        },

        emitModelValue() {
            this.$root.dispatchEvent(new CustomEvent('input', { detail: this.isoValue, bubbles: true }));
        },

        openPicker() {
            if (this.isDisabled || this.$refs.displayInput?.disabled) return;

            if (this.flatpickrInstance) {
                this.flatpickrInstance.open();
                return;
            }

            const picker = this.$refs.picker;
            if (! picker) return;

            picker.value = this.isoValue;
            if (typeof picker.showPicker === 'function') {
                try {
                    picker.showPicker();
                    return;
                } catch (error) {
                    return;
                }
            }

            picker.focus();
            picker.click();
        },

        syncFlatpickrDate(value) {
            if (! this.flatpickrInstance) return;

            if (! value) {
                this.flatpickrInstance.clear(false);
                return;
            }

            this.flatpickrInstance.setDate(value, false, 'Y-m-d');
        },

        isAllowedDateObject(date) {
            const isoDate = dateToIso(date);
            if (this.minDate && isoDate < this.minDate) return false;
            if (this.maxDate && isoDate > this.maxDate) return false;
            if (this.allowedWeekdays.length === 0) return true;

            return this.allowedWeekdays.includes(isoWeekday(date));
        },

        isAllowedIso(value) {
            const parsed = isoToDate(value);

            return parsed ? this.isAllowedDateObject(parsed) : false;
        },

        toIso(value) {
            const parsed = displayToDate(value, this.mode);
            if (! parsed) return null;

            const date = dateToIso(parsed);
            if (this.minDate && date < this.minDate) return null;
            if (this.maxDate && date > this.maxDate) return null;
            if (this.mode !== 'datetime' && ! this.isAllowedDateObject(parsed)) return null;
            if (this.mode !== 'datetime') return date;

            const hour = String(parsed.getHours()).padStart(2, '0');
            const minute = String(parsed.getMinutes()).padStart(2, '0');

            return `${date}T${hour}:${minute}`;
        },

        fromIso(value) {
            return isoToDisplay(value, this.mode);
        },
    };
}

export function registerLocalDateInput() {
    window.localDateInput ??= createLocalDateInput;
}
