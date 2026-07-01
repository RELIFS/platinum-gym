function adminBookingForm(initialDate, scheduleOptions = [], memberScheduleAccess = {}, selectedMemberId = '', selectedScheduleId = '', minimumDate = null) {
    return {
        submitting: false,
        sessionDate: initialDate,
        sessionDateDisplay: '',
        minimumDate,
        scheduleOptions: Array.isArray(scheduleOptions) ? scheduleOptions : [],
        memberScheduleAccess: memberScheduleAccess || {},
        selectedMemberId: String(selectedMemberId || ''),
        selectedScheduleId: String(selectedScheduleId || ''),
        init() {
            this.sessionDateDisplay = this.fromIso(this.sessionDate);
            this.resetInvalidSchedule();
        },
        get eligibleSchedules() {
            if (! this.selectedMemberId) return [];

            const allowed = this.memberScheduleAccess[this.selectedMemberId] || [];

            return this.scheduleOptions.filter((schedule) => allowed.includes(String(schedule.id)));
        },
        get scheduleDisabled() {
            return ! this.selectedMemberId || this.eligibleSchedules.length === 0;
        },
        get schedulePlaceholder() {
            if (! this.selectedMemberId) return 'Pilih member terlebih dahulu';
            if (this.eligibleSchedules.length === 0) return 'Tidak ada jadwal sesuai paket member';

            return 'Pilih jadwal';
        },
        get selectedSchedule() {
            return this.scheduleOptions.find((schedule) => String(schedule.id) === String(this.selectedScheduleId)) || null;
        },
        get selectedScheduleDay() {
            return Number(this.selectedSchedule?.day_of_week || 0);
        },
        get dateDisabled() {
            return ! this.selectedScheduleDay;
        },
        isScheduleEligible(scheduleId) {
            return this.eligibleSchedules.some((schedule) => String(schedule.id) === String(scheduleId));
        },
        setMember(memberId) {
            this.selectedMemberId = String(memberId || '');
            this.resetInvalidSchedule();
            this.$nextTick(() => this.syncDate(this.$refs.scheduleSelect, false));
        },
        resetInvalidSchedule() {
            if (this.selectedScheduleId && ! this.isScheduleEligible(this.selectedScheduleId)) {
                this.selectedScheduleId = '';
                this.sessionDate = '';
                this.sessionDateDisplay = '';
            }
        },
        syncDate(select, shouldUpdate = true) {
            const option = select?.selectedOptions?.[0];
            const targetIso = Number(option?.dataset?.dayOfWeek || this.selectedScheduleDay || 0);
            if (! targetIso) {
                this.sessionDate = '';
                this.sessionDateDisplay = '';
                return;
            }

            if (! shouldUpdate && this.sessionDate && this.isDateAllowedForIso(this.sessionDate, targetIso)) {
                this.sessionDateDisplay = this.fromIso(this.sessionDate);
                return;
            }

            const nextDate = this.nextDateForIso(targetIso, this.sessionDate || this.today());
            if (shouldUpdate || ! this.sessionDate || ! this.isDateAllowedForIso(this.sessionDate, targetIso)) {
                this.sessionDate = nextDate;
                this.sessionDateDisplay = this.fromIso(nextDate);
            }
        },
        syncIsoFromDisplay() {
            const digits = this.sessionDateDisplay.replace(/\D/g, '').slice(0, 8);
            const day = digits.slice(0, 2);
            const month = digits.slice(2, 4);
            const year = digits.slice(4, 8);
            this.sessionDateDisplay = [day, month, year].filter(Boolean).join('/');
            this.sessionDate = this.toIso(this.sessionDateDisplay) || '';
        },
        nextDateForIso(targetIso, fromDate) {
            let parsed = this.parseLocalDate(fromDate) || this.parseLocalDate(this.minimumDate) || this.parseLocalDate(this.today());
            const minimum = this.parseLocalDate(this.minimumDate) || this.parseLocalDate(this.today());
            if (parsed < minimum) parsed = minimum;

            const jsDay = parsed.getDay();
            const currentIso = jsDay === 0 ? 7 : jsDay;
            const diff = (targetIso - currentIso + 7) % 7;
            parsed.setDate(parsed.getDate() + diff);

            return this.formatLocalDate(parsed);
        },
        parseLocalDate(value) {
            if (! value) return null;

            const parsed = new Date(`${value}T00:00:00`);

            return Number.isNaN(parsed.getTime()) ? null : parsed;
        },
        formatLocalDate(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        },
        isDateAllowedForIso(value, targetIso) {
            const parsed = this.parseLocalDate(value);
            const minimum = this.parseLocalDate(this.minimumDate) || this.parseLocalDate(this.today());
            if (! parsed || parsed < minimum) return false;

            const jsDay = parsed.getDay();
            const currentIso = jsDay === 0 ? 7 : jsDay;

            return currentIso === targetIso;
        },
        fromIso(value) {
            const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

            return match ? `${match[3]}/${match[2]}/${match[1]}` : '';
        },
        toIso(value) {
            const match = String(value || '').match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            if (! match) return null;

            const day = Number(match[1]);
            const month = Number(match[2]);
            const year = Number(match[3]);
            const parsed = new Date(Date.UTC(year, month - 1, day));
            if (parsed.getUTCFullYear() !== year || parsed.getUTCMonth() + 1 !== month || parsed.getUTCDate() !== day) return null;

            return `${year.toString().padStart(4, '0')}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        },
        today() {
            return this.formatLocalDate(new Date());
        },
    };
}

function adminMemberCombobox(options, selectedId = '') {
    return {
        options: Array.isArray(options) ? options : [],
        selectedId,
        query: '',
        open: false,
        highlighted: 0,
        init() {
            const selected = this.options.find((option) => option.id === this.selectedId);
            this.query = selected?.label || '';
            this.clampHighlighted();
        },
        get filtered() {
            const needle = this.query.toLowerCase().trim();
            const selected = this.options.find((option) => option.id === this.selectedId);
            const results = selected && this.query === selected.label
                ? this.options
                : this.options.filter((option) => option.label.toLowerCase().includes(needle));

            return results.slice(0, 20);
        },
        get activeOptionId() {
            const option = this.filtered[this.highlighted];

            return this.open && option ? `admin-booking-member-option-${option.id}` : null;
        },
        search() {
            this.open = true;
            this.highlighted = 0;
            this.clampHighlighted();
        },
        choose(option) {
            this.selectedId = option.id;
            this.query = option.label;
            this.open = false;
            this.clampHighlighted();
            this.$dispatch('admin-member-selected', { memberId: this.selectedId });
        },
        chooseHighlighted() {
            this.clampHighlighted();
            const option = this.filtered[this.highlighted];
            if (option) this.choose(option);
        },
        move(direction) {
            this.open = true;
            const count = this.filtered.length;
            if (! count) {
                this.highlighted = 0;

                return;
            }

            this.highlighted = (this.highlighted + direction + count) % count;
        },
        clear() {
            this.selectedId = '';
            this.query = '';
            this.highlighted = 0;
            this.open = true;
            this.$dispatch('admin-member-selected', { memberId: '' });
            this.$nextTick(() => this.$refs.memberSearch?.focus());
        },
        clampHighlighted() {
            const count = this.filtered.length;
            if (! count) {
                this.highlighted = 0;

                return;
            }

            this.highlighted = Math.min(Math.max(this.highlighted, 0), count - 1);
        },
    };
}

function adminCashPaymentForm(trainerOptionsByPackage = {}, packageRules = {}, selectedPackageId = '', selectedTrainerId = '') {
    return {
        packageId: String(selectedPackageId || ''),
        trainerId: String(selectedTrainerId || ''),
        trainerOptionsByPackage: trainerOptionsByPackage || {},
        packageRules: packageRules || {},
        init() {
            this.syncTrainer();
        },
        get selectedPackageRule() {
            return this.packageRules[this.packageId] || { requires_trainer: false, trainer_specialization: null };
        },
        get trainerRequired() {
            return Boolean(this.selectedPackageRule.requires_trainer);
        },
        get trainerDisabled() {
            return ! this.trainerRequired;
        },
        get trainerOptions() {
            return this.trainerOptionsByPackage[this.packageId] || [];
        },
        get trainerPlaceholder() {
            if (! this.packageId) return 'Pilih paket terlebih dahulu';
            if (! this.trainerRequired) return 'Tidak perlu trainer';
            if (this.trainerOptions.length === 0) return 'Belum ada trainer sesuai paket';

            return 'Pilih trainer';
        },
        syncTrainer() {
            if (! this.trainerRequired) {
                this.trainerId = '';

                return;
            }

            const stillAvailable = this.trainerOptions.some((trainer) => String(trainer.id) === String(this.trainerId));
            if (! stillAvailable) {
                this.trainerId = '';
            }
        },
    };
}

export function registerAdminBookingForms() {
    window.adminBookingForm ??= adminBookingForm;
    window.adminMemberCombobox ??= adminMemberCombobox;
    window.adminCashPaymentForm ??= adminCashPaymentForm;
}
