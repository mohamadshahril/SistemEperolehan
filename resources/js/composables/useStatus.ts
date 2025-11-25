// resources/js/composables/useStatus.ts
import { CheckCircle, XCircle, Clock, HelpCircle } from 'lucide-vue-next';

export function useStatus() {
    function statusClass(status?: string | null): string {
        const s = (status || '').toLowerCase();
        switch (s) {
            case 'approved':
                return 'bg-green-100 text-green-800 border border-green-200';
            case 'rejected':
                return 'bg-red-100 text-red-800 border border-red-200';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
            default:
                return 'bg-gray-100 text-gray-800 border border-gray-200';
        }
    }

    function statusLabel(status?: string | null): string {
        const s = (status || '').toLowerCase();
        switch (s) {
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            case 'pending':
                return 'Pending Review';
            default:
                return 'Unknown';
        }
    }

    function statusIcon(status?: string | null) {
        const s = (status || '').toLowerCase();
        switch (s) {
            case 'approved':
                return CheckCircle;
            case 'rejected':
                return XCircle;
            case 'pending':
                return Clock;
            default:
                return HelpCircle;
        }
    }

    return { statusClass, statusLabel, statusIcon };
}
