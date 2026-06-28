function memberBookingForm(targetIso, initialDate, minimumDate = null) {
    return {
        sessionDate: initialDate,
        minimumDate,
        snapping: false,
        init() {
            this.snapToDay();
            this.$watch('sessionDate', (value, oldValue) => {
                if (this.snapping || value === oldValue) return;
                this.snapToDay();
            });
        },
        snapToDay() {
            if (! this.sessionDate) return;

            let parsed = new Date(`${this.sessionDate}T00:00:00`);
            if (Number.isNaN(parsed.getTime())) return;
            const minimum = this.parseLocalDate(this.minimumDate);
            const wasBeforeMinimum = minimum && parsed < minimum;
            if (wasBeforeMinimum) parsed = minimum;

            const jsDay = parsed.getDay();
            const iso = jsDay === 0 ? 7 : jsDay;
            if (iso === targetIso) {
                if (wasBeforeMinimum) {
                    this.sessionDate = this.formatLocalDate(parsed);
                }

                return;
            }

            const diff = (targetIso - iso + 7) % 7 || 7;
            const next = new Date(parsed.getTime());
            next.setDate(next.getDate() + diff);

            this.snapping = true;
            this.sessionDate = this.formatLocalDate(next);
            this.$nextTick(() => {
                this.snapping = false;
            });
        },
        formatLocalDate(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        },
        parseLocalDate(value) {
            if (! value) return null;

            const parsed = new Date(`${value}T00:00:00`);

            return Number.isNaN(parsed.getTime()) ? null : parsed;
        },
    };
}

export function registerMemberBookingForms() {
    window.memberBookingForm ??= memberBookingForm;
}
