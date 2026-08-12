<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Rocket, ChevronLeft, Save } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { can } from '@/lib/can';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { HelpCircle } from "@lucide/vue";

const props = defineProps({
  assessment: Object as () => any,
  govName: String,
  debtService: Object as () => any,
});

const form = useForm({
  plafond: props.debtService?.plafond || 0,
  tenor: props.debtService?.tenor || 0,
  disbursement_period: props.debtService?.disbursement_period || 0,
  first_disbursement: props.debtService?.first_disbursement || '',
  interest: props.debtService?.interest || 0,
});

const submit = () => {
  form.put(`/assessment-details/${props.assessment.id}/debt-service`, {
    preserveScroll: true,
  });
};

const formatCurrency = (value: any) => {
  if (!value && value !== 0) return '0';
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(value) || 0);
};

const parseNumber = (val: string): number => {
  const cleaned = val.replace(/\./g, '').replace(',', '.');
  return Number(cleaned) || 0;
};

const handleCurrencyFocus = (e: FocusEvent) => {
  const input = e.target as HTMLInputElement;
  const raw = parseNumber(input.value);
  input.value = raw === 0 ? '' : String(raw);
};

const handlePlafondInput = (e: Event) => {
  const input = e.target as HTMLInputElement;
  form.plafond = parseNumber(input.value);
};

const handlePlafondBlur = (e: FocusEvent) => {
  const input = e.target as HTMLInputElement;
  form.plafond = parseNumber(input.value);
  input.value = formatCurrency(form.plafond);
};
</script>

<template>

  <Head title="Edit Debt Service Coverage Ratio (DSCR)" />

  <Alert class="m-2 bg-gray-100" v-if="$page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ $page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <!-- <AssessmentStepper :assessment-id="assessment.id" :current-step="7" /> -->

  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div class="max-w-5xl mx-auto">
      <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">

        <div class="bg-teal-600 text-white py-4 text-center shadow-inner">
          <div class="flex items-center justify-center gap-2">
            <h1 class="text-2xl font-bold text-center">Debt Service Coverage Ratio (DSCR)</h1>
            <HoverCard>
              <HoverCardTrigger>
                <HelpCircle :size="20" class="text-white" />
              </HoverCardTrigger>
              <HoverCardContent>
                Pada DSCR (Debt Service Coverage Ratio), Saudara diminta untuk memberikan data perencanaan pinjaman
                mulai
                dari plafon pinjaman, tenor pinjaman, tanggal pencairan pertama serta rate atas pinjaman.
              </HoverCardContent>
            </HoverCard>
          </div>
          <p class="text-center text-sm font-medium text-teal-100 mt-1">Input-Output</p>
        </div>

        <form @submit.prevent="submit" class="p-6">
          <!-- Input Section -->
          <div>
            <h2 class="text-lg font-bold mb-4 border-b pb-2">Input:</h2>
            <div class="grid grid-cols-12 gap-x-4 gap-y-3 items-center">

              <div class="col-span-4 lg:col-span-3 text-sm"><Label>Nama Pemda</Label></div>
              <div class="col-span-8 lg:col-span-9 flex items-center gap-2">
                <span>:</span> <span class="font-medium px-3 py-1">{{ govName }}</span>
              </div>

              <div class="col-span-4 lg:col-span-3 text-sm"><Label>Plafon Pinjaman</Label></div>
              <div class="col-span-8 lg:col-span-9 flex items-center gap-2">
                <span>:</span>
                <input type="text" :value="formatCurrency(form.plafond)" @input="handlePlafondInput"
                  @focus="handleCurrencyFocus" @blur="handlePlafondBlur"
                  class="flex h-9 rounded-md border border-input px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring bg-orange-200 border-orange-300 w-1/3 text-right" />
                <span class="text-sm italic w-full text-muted-foreground">(diisi dengan Rupiah penuh)</span>
              </div>

              <div class="col-span-4 lg:col-span-3 text-sm"><Label>Tenor Pinjaman</Label></div>
              <div class="col-span-8 lg:col-span-9 flex items-center gap-2">
                <span>:</span>
                <Input type="number" v-model="form.tenor" class="bg-orange-200 border-orange-300 w-1/3" />
                <span class="text-sm italic w-full text-muted-foreground">(diisi dalam satuan tahun)</span>
              </div>

              <div class="col-span-4 lg:col-span-3 text-sm"><Label>Masa Pencairan Pinjaman</Label></div>
              <div class="col-span-8 lg:col-span-9 flex items-center gap-2">
                <span>:</span>
                <Input type="number" v-model="form.disbursement_period" class="bg-orange-200 border-orange-300 w-1/3" />
                <span class="text-sm italic w-full text-muted-foreground">(diisi dalam satuan bulan)</span>
              </div>

              <div class="col-span-4 lg:col-span-3 text-sm"><Label>Tanggal Pencairan Pertama</Label></div>
              <div class="col-span-8 lg:col-span-9 flex items-center gap-2">
                <span>:</span>
                <Input type="date" v-model="form.first_disbursement" class="bg-orange-200 border-orange-300 w-1/3" />
              </div>

              <div class="col-span-4 lg:col-span-3 text-sm"><Label>Bunga (%)</Label></div>
              <div class="col-span-8 lg:col-span-9 flex items-center gap-2">
                <span>:</span>
                <Input type="number" step="0.01" v-model="form.interest"
                  class="bg-orange-200 border-orange-300 w-1/3" />
                <span class="text-sm italic w-full text-muted-foreground">%</span>
              </div>

            </div>
          </div>

          <!-- Output Section -->
          <div v-if="debtService && debtService.id" class="border-t pt-8 border-gray-200 mt-8">
            <h2 class="text-lg font-bold mb-4 border-b pb-2">Output:</h2>
            <div class="grid grid-cols-12 gap-x-4 gap-y-3 items-center">

              <div class="col-span-5 lg:col-span-4 text-sm"><Label>DSCR APBD 2025</Label></div>
              <div class="col-span-7 lg:col-span-8 flex items-center gap-2">
                <span>:</span>
                <div class="bg-green-100 px-3 py-1.5 rounded border border-green-200 w-1/3 shadow-sm">{{
                  (debtService.dscr || 0).toFixed(2) }} kali</div>
              </div>

              <div class="col-span-5 lg:col-span-4 text-sm"><Label>Batas Maksimal Pinjaman</Label></div>
              <div class="col-span-7 lg:col-span-8 flex items-center gap-2">
                <span>:</span>
                <div class="px-3 py-1.5 w-1/3 font-medium">{{ formatCurrency(debtService.max_loan) }}</div>
              </div>

              <div class="col-span-5 lg:col-span-4 text-sm"><Label>% Pinjaman Terhadap Batas Maksimal Pinjaman</Label>
              </div>
              <div class="col-span-7 lg:col-span-8 flex items-center gap-2">
                <span>:</span>
                <div class="bg-green-100 px-3 py-1.5 rounded border border-green-200 w-1/3 shadow-sm">{{
                  (debtService.limit || 0).toFixed(0) }}%</div>
                <span class="text-sm italic text-muted-foreground ml-2">Maksimal 75%</span>
              </div>

              <div class="col-span-5 lg:col-span-4 text-sm"><Label>Rata-rata pengembalian pokok per tahun</Label></div>
              <div class="col-span-7 lg:col-span-8 flex items-center gap-2">
                <span>:</span>
                <div class="px-3 py-1.5 font-medium">{{ formatCurrency(debtService.avg_annual_return) }}</div>
              </div>

              <div class="col-span-5 lg:col-span-4 text-sm"><Label>Rata-rata pembayaran bunga per tahun</Label></div>
              <div class="col-span-7 lg:col-span-8 flex items-center gap-2">
                <span>:</span>
                <div class="px-3 py-1.5 font-medium">{{ formatCurrency(debtService.avg_annual_interest) }}</div>
              </div>

              <div class="col-span-5 lg:col-span-4 text-sm"><Label>Rata-rata pembayaran biaya per tahun</Label></div>
              <div class="col-span-7 lg:col-span-8 flex items-center gap-2">
                <span>:</span>
                <div class="px-3 py-1.5 font-medium">{{ formatCurrency(debtService.avg_annual_cost) }}</div>
              </div>

              <div class="col-span-5 lg:col-span-4 text-sm"><Label>Rata-rata pembayaran pinjaman eksisting</Label></div>
              <div class="col-span-7 lg:col-span-8 flex items-center gap-2">
                <span>:</span>
                <div class="px-3 py-1.5 font-medium">{{ formatCurrency(debtService.avg_exis_payment) }}</div>
              </div>

            </div>
          </div>

          <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-8 -mx-6 -mb-6">
            <!-- <Link href="/assessments">
              <Button type="button" variant="outline" class="flex items-center gap-2">
                <ChevronLeft :size="16" /> Back
              </Button>
            </Link> -->
            <div v-if="can('create-assessments')">
              <Button type="submit" :disabled="form.processing"
                class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
                <Save :size="16" /> Simpan dan Lanjutkan
              </Button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>
