<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Rocket, Save, ArrowLeft } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { can } from '@/lib/can';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { HelpCircle } from "@lucide/vue";

const props = defineProps({
  assessment: Object as () => any,
  selfAssessment: Object as () => any,
  ratingResult: Object as () => any, // From QuantitativeRatingService
  indicativeRating: String, // Pre-calculated label from backend
});

const form = useForm({
  economy_condition: props.assessment?.economy_condition?.toFixed(2) || props.ratingResult?.skor_ekonomi?.toFixed(2) || 0,
  financial_condition: props.assessment?.financial_condition?.toFixed(2) || props.ratingResult?.skor_keuangan?.toFixed(2) || 0,
  indicative_rating: props.assessment?.indicative_rating || props.indicativeRating || '',
  final_rating: props.assessment?.final_rating || props.indicativeRating || '',
  selfAssessment: {
    social_politics_disturbance: props.selfAssessment?.social_politics_disturbance || '0',
    social_politics_disturbance_detail: props.selfAssessment?.social_politics_disturbance_detail || '',
    arrears_restructuring: props.selfAssessment?.arrears_restructuring || '0',
    arrears_restructuring_detail: props.selfAssessment?.arrears_restructuring_detail || '',
    cashflow_availability: props.selfAssessment?.cashflow_availability || '0',
    cashflow_availability_detail: props.selfAssessment?.cashflow_availability_detail || '',
    advantage: props.selfAssessment?.advantage || '',
    challenge: props.selfAssessment?.challenge || '',
  }
});

// Automatic calculation for ratings (client side simplified or just use backend result)
// Since the rating logic is complex (in PHP services), we'll stick to the backend's initial value
// but update final_rating if self assessment changes?
// For now, let's keep it manual or synchronized.

watch(() => form.selfAssessment, () => {
  // If any self assessment is 'Ya', maybe downgrade?
  // User didn't specify, so we allow manual adjustment of final_rating for now
  // but default it to indicative_rating if it's the first time.

  // Parse values to numbers to avoid string concatenation
  const soc_dist = Number(form.selfAssessment.social_politics_disturbance || 0) + (props.ratingResult?.peringkat || 0);
  const arr_res = Number(form.selfAssessment.arrears_restructuring || 0) + (props.ratingResult?.peringkat || 0);
  const cash_ava = Number(form.selfAssessment.cashflow_availability || 0) + (props.ratingResult?.peringkat || 0);

  // Use Math.max instead of max
  const basePeringkat = Number(props.ratingResult?.peringkat || 0);
  const qual_score = Math.max(soc_dist, arr_res, cash_ava);
  const final_score = Math.max(qual_score, basePeringkat);

  // Apply downgrade logic using ranges to ensure reactivity for all values
  if (final_score <= 2) {
    form.final_rating = 'Sangat Memadai';
  } else if (final_score <= 4) {
    form.final_rating = 'Memadai';
  } else if (final_score <= 6) {
    form.final_rating = 'Kurang Memadai';
  } else {
    form.final_rating = 'Sangat Kurang Memadai';
  }

}, { deep: true });

const submit = () => {
  form.put(`/assessment-details/${props.assessment.id}/indicative-rating`, {
    preserveScroll: true,
  });
};

const formatNumber = (val: number) => {
  return Number(val).toFixed(2);
}

</script>

<template>

  <Head title="Edit Indikasi Rating Daerah" />

  <Alert class="m-2 bg-gray-100" v-if="$page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ $page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <AssessmentStepper :assessment-id="assessment.id" :current-step="7" />

  <div class="p-6 bg-gray-50 min-h-screen font-sans text-sm">
    <div class="max-w-[1000px] mx-auto">
      <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
        
        <div class="bg-teal-600 text-white py-4 text-center shadow-inner">
          <div class="flex items-center justify-center gap-2">
            <h1 class="text-2xl font-bold">Indikasi Rating Pemda</h1>
            <HoverCard>
              <HoverCardTrigger>
                <HelpCircle :size="20" class="text-white" />
              </HoverCardTrigger>
              <HoverCardContent>
                Pada DSCR (Debt Service Coverage Ratio), Saudara diminta untuk memberikan data perencanaan pinjaman mulai
                dari plafon pinjaman, tenor pinjaman, tanggal pencairan pertama serta rate atas pinjaman, diakhiri dengan
                kesimpulan atas perhitungan Debt Service Coverage Ratio (DSCR), tantangan dan rencana aksi sebagai hasil
                akhir tahap ini.
              </HoverCardContent>
            </HoverCard>
          </div>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-6">
      <!-- Scores Section -->
      <div class="bg-gray-50/50 p-6 border border-gray-200 rounded-lg grid grid-cols-12 gap-4 items-center">
        <label class="col-span-6 font-medium">Kondisi Ekonomi</label>
        <div class="col-span-6">
          <Input type="number" step="0.01" v-model="form.economy_condition" class="bg-gray-50 text-right" readonly />
        </div>

        <label class="col-span-6 font-medium">Kondisi Keuangan dan Manajemen Keuangan</label>
        <div class="col-span-6">
          <Input type="number" step="0.01" v-model="form.financial_condition" class="bg-gray-50 text-right" readonly />
        </div>

        <label class="col-span-6 font-medium">Indikasi Rating</label>
        
        <div class="col-span-6">
          <div class="border px-3 py-2 bg-yellow-100 text-yellow-800 font-bold text-center rounded-sm">
            {{ form.indicative_rating }}
          </div>
        </div>
      </div>

      <!-- Self Assessment Section -->
      <div class="bg-gray-50/50 p-6 border border-gray-200 rounded-lg space-y-4">
        <h2 class="font-bold italic text-teal-800">Self Assessment</h2>

        <!-- Question 1 -->
        <div class="space-y-2">
          <div class="flex justify-between items-start gap-4">
            <span class="flex-1">Gangguan sosio politik yang dapat berdampak buruk pada pemenuhan kewajiban jatuh
              tempo</span>
            <Select v-model="form.selfAssessment.social_politics_disturbance">
              <SelectTrigger class="w-[120px] bg-orange-100 border-orange-200">
                <SelectValue placeholder="Pilih" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Tidak</SelectItem>
                <SelectItem value="1">Berpotensi terjadi dengan dampak tidak signifikan</SelectItem>
                <SelectItem value="2">Terjadi dengan dampak signifikan</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex gap-2 items-center">
            <span class="text-xs text-gray-500 w-32">Penjelasan kondisi</span>
            <Input v-model="form.selfAssessment.social_politics_disturbance_detail"
              class="bg-orange-50 border-orange-200 h-8 text-xs" />
          </div>
        </div>

        <!-- Question 2 -->
        <div class="space-y-2 pt-2 border-t border-dashed">
          <div class="flex justify-between items-start gap-4">
            <span class="flex-1">Riwayat tunggakan dan restrukturisasi pembiayaan di Pemerintah Pusat, PT SMI, dan
              Lembaga Keuangan lainnya</span>
            <Select v-model="form.selfAssessment.arrears_restructuring">
              <SelectTrigger class="w-[120px] bg-orange-100 border-orange-200">
                <SelectValue placeholder="Pilih" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Tidak</SelectItem>
                <SelectItem value="2">Pernah memiliki tunggakan dalam 1 tahun terakhir</SelectItem>
                <SelectItem value="7">Sedang memiliki tunggakan <90 hari</SelectItem>
                    <SelectItem value="8">Sedang memiliki tunggakan >90 hari</SelectItem>
                    <SelectItem value="1">Pernah atau sedang mengalami restrukturisasi</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex gap-2 items-center">
            <span class="text-xs text-gray-500 w-32">Penjelasan kondisi</span>
            <Input v-model="form.selfAssessment.arrears_restructuring_detail"
              class="bg-orange-50 border-orange-200 h-8 text-xs" />
          </div>
        </div>

        <!-- Question 3 -->
        <div class="space-y-2 pt-2 border-t border-dashed">
          <div class="flex justify-between items-start gap-4">
            <span class="flex-1">Keterbatasan kesediaan arus kas untuk pemenuhan kewajiban yang akan jatuh tempo</span>
            <Select v-model="form.selfAssessment.cashflow_availability">
              <SelectTrigger class="w-[120px] bg-orange-100 border-orange-200">
                <SelectValue placeholder="Pilih" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Tidak</SelectItem>
                <SelectItem value="1">Berpotensi terjadi dengan dampak tidak signifikan
                </SelectItem>
                <SelectItem value="2">Terjadi dengan dampak signifikan</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex gap-2 items-center">
            <span class="text-xs text-gray-500 w-32">Penjelasan kondisi</span>
            <Input v-model="form.selfAssessment.cashflow_availability_detail"
              class="bg-orange-50 border-orange-200 h-8 text-xs" />
          </div>
        </div>

        <!-- Final Rating Row -->
        <div class="flex items-center gap-4 pt-6">
          <label class="font-bold">Indikasi Rating Final</label>
          <div class="flex-1 max-w-[200px]">
            <div class="border px-3 py-2 bg-yellow-100 text-yellow-800 font-bold text-center rounded-sm">
              {{ form.final_rating }}
            </div>
          </div>
        </div>
      </div>

      <!-- Conclusion Section -->
      <div class="bg-gray-50/50 p-6 border border-gray-200 rounded-lg space-y-4">
        <div class="bg-teal-600/10 border-l-4 border-teal-600 p-2 font-bold text-teal-800">
          Kesimpulan:
        </div>

        <div class="space-y-1">
          <label class="font-medium">Apa saja yang sudah baik dari aspek ekonomi dan fiskal?</label>
          <Textarea v-model="form.selfAssessment.advantage" class="bg-orange-100/50 border-orange-200 min-h-[80px]" />
        </div>

        <div class="space-y-1">
          <label class="font-medium">Apa saja yang masih menjadi tantangan serta membutuhkan perbaikan dari aspek
            ekonomi dan fiskal?</label>
          <Textarea v-model="form.selfAssessment.challenge" class="bg-orange-100/50 border-orange-200 min-h-[80px]" />
        </div>
      </div>

      <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-8 -mx-6 -mb-6">
        <Link href="/assessments">
          <Button type="button" variant="outline" class="flex items-center gap-2">
            <ArrowLeft :size="16" /> Back
          </Button>
        </Link>
        <div v-if="can('create-assessments')">
          <Button type="submit" :disabled="form.processing" class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
            <Save :size="16" /> Simpan dan Lanjutkan
          </Button>
        </div>
      </div>
    </form>
    </div>
  </div>
</div>
</template>
