import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AssessmentStepper from '@/components/AssessmentStepper.vue';

describe('AssessmentStepper.vue', () => {
    it('renders the correct number of steps and the current step label', () => {
        const wrapper = mount(AssessmentStepper, {
            props: {
                assessmentId: 1,
                currentStep: 2, // Infrastruktur
            },
        });

        // Verifikasi label progress bar
        expect(wrapper.text()).toContain('Langkah 2 dari 9');

        // Verifikasi bahwa langkah aktif ditandai dengan benar
        expect(wrapper.text()).toContain('Infrastruktur');
        
        // Cek "Lanjut" button teks
        expect(wrapper.text()).toContain('Lanjut: Data Keuangan');
    });

    it('hides previous button on first step', () => {
        const wrapper = mount(AssessmentStepper, {
            props: {
                assessmentId: 1,
                currentStep: 1, // Data Pemda
            },
        });

        // Verifikasi teks "Lanjut" ada
        expect(wrapper.text()).toContain('Lanjut: Infrastruktur');
        
        // Verifikasi tidak ada teks untuk tombol sebelumnya (hanya teks fallback '—')
        expect(wrapper.text()).toContain('—');
    });

    it('shows completed text on the last step', () => {
        const wrapper = mount(AssessmentStepper, {
            props: {
                assessmentId: 1,
                currentStep: 9, // Laporan
            },
        });

        // Verifikasi tombol prev ada
        expect(wrapper.text()).toContain('Rencana Aksi'); // prev label

        // Verifikasi indikator selesai di akhir
        expect(wrapper.text()).toContain('Semua langkah selesai');
    });
});
