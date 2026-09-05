<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, Save, Plus, HelpCircle, Trash } from '@lucide/vue';
import { computed, ref } from 'vue';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card"
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { can } from '@/lib/can';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Ekonomi',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    assessment: { id: number },
    govName: string,
    govLevel: string,
    year: number,
    economyIndicator: any,
    sectoralGdp: any,
    comparison: any,
}>();

const form = useForm({
    year: props.year,
    economyIndicator: { ...props.economyIndicator },
    sectoralGdp: { ...props.sectoralGdp },
});

const submit = () => {
    form.economyIndicator.gdp = totalGdp.value;
    form.put(`/assessment-details/${props.assessment.id}/economy-condition`);
};

const isModalOpen = ref(false);
const modalYear = ref((props.year + 1).toString());
const isDeleteDialogOpen = ref(false);

const deleteYearData = () => {
    router.delete(`/assessment-details/${props.assessment.id}/economy-condition`, {
        data: { year: props.year },
        preserveScroll: true,
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
        },
    });
};

const availableYears = computed(() => {
    const range = [];
    for (let i = 1; i <= 3; i++) {
        range.push(props.year + i);
    }
    return range;
});

const startNewYear = () => {
    const year = Number(modalYear.value);
    if (!year) return;

    // Clear the form and set the new year
    form.year = year;
    form.economyIndicator = {
        poverty: 0,
        unemployment: 0,
        gdp_growth: 0,
        gdp_perkapita: 0,
        hdci: 0,
        gdp: 0,
    };

    // Reset all sectoral GDP to 0
    const emptySectors: any = {};
    sectors.forEach(s => {
        emptySectors[s.key] = 0;
    });
    form.sectoralGdp = emptySectors;

    isModalOpen.value = false;
};

// Sectors definition
const sectors = [
    { key: 'agriculture_forestry_fishery', label: '1. Pertanian, Kehutanan, dan Perikanan' },
    { key: 'mining_quarrying', label: '2. Pertambangan dan Penggalian' },
    { key: 'processing_industry', label: '3. Industri Pengolahan' },
    { key: 'electricity_gas', label: '4. Pengadaan Listrik dan Gas' },
    { key: 'water_waste', label: '5. Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang' },
    { key: 'contruction', label: '6. Konstruksi' },
    { key: 'trade_vehicle_repair', label: '7. Perdagangan Besar dan Eceran; Reparasi Mobil dan Sepeda Motor' },
    { key: 'transportation_warehousing', label: '8. Transportasi dan Pergudangan' },
    { key: 'acomodation_food_beverage', label: '9. Penyediaan Akomodasi dan Makan Minum' },
    { key: 'information_communication', label: '10. Informasi dan Komunikasi' },
    { key: 'finance_insurance', label: '11. Jasa Keuangan dan Asuransi' },
    { key: 'real_estate', label: '12. Real Estat' },
    { key: 'company_service', label: '13. Jasa Perusahaan' },
    { key: 'gov_adm_defense_sosial_security', label: '14. Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib' },
    { key: 'education_service', label: '15. Jasa Pendidikan' },
    { key: 'health_social_service', label: '16. Jasa Kesehatan dan Kegiatan Sosial' },
    { key: 'other_service', label: '17. Jasa lainnya' },
];

const totalGdp = computed(() => {
    let total = 0;
    sectors.forEach(s => {
        total += parseFloat(form.sectoralGdp[s.key] || 0);
    });
    return total;
});

const sectorPercentages = computed(() => {
    const total = totalGdp.value;
    let percentages: Record<string, number> = {};
    sectors.forEach(s => {
        if (total > 0) {
            percentages[s.key] = (parseFloat(form.sectoralGdp[s.key] || 0) / total) * 100;
        } else {
            percentages[s.key] = 0;
        }
    });
    return percentages;
});

const gdpConcentration = computed(() => {
    let sum = 0;
    sectors.forEach(s => {
        let p = sectorPercentages.value[s.key] || 0;
        sum += Math.pow(p, 2);
    });
    return sum;
});

const formatNumber = (num: number, decimals: number = 0) => {
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(num);
};

const formatCurrency = (val: any) => {
    if (val === undefined || val === null) return '0';
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(val) || 0);
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

const handleSectorInput = (e: Event, key: string) => {
    const input = e.target as HTMLInputElement;
    const num = parseNumber(input.value);
    form.sectoralGdp[key] = num;
};

const handleSectorBlur = (e: FocusEvent, key: string) => {
    const input = e.target as HTMLInputElement;
    const num = parseNumber(input.value);
    form.sectoralGdp[key] = num;
    input.value = formatCurrency(num);
};

// Qualitatives based on generic estimates
const povertyLabel = computed(() => {
    const v = parseFloat(form.economyIndicator.poverty || 0);
    if (v < 5) return 'Sangat Rendah';
    if (v < 10) return 'Rendah';
    if (v < 15) return 'Sedang';
    if (v < 20) return 'Tinggi';
    return 'Sangat Tinggi';
});

const unemploymentLabel = computed(() => {
    const v = parseFloat(form.economyIndicator.unemployment || 0);
    if (v < 3) return 'Sangat Rendah';
    if (v < 5) return 'Rendah';
    if (v < 8) return 'Rata-rata/ Sedang';
    if (v < 10) return 'Tinggi';
    return 'Sangat Tinggi';
});

const hdciLabel = computed(() => {
    const v = parseFloat(form.economyIndicator.hdci || 0);
    if (v >= 80) return 'Sangat Tinggi';
    if (v >= 70) return 'Rata-rata/ Sedang';
    if (v >= 60) return 'Sedang';
    return 'Rendah';
});

const gdpPerkapitaLabel = computed(() => {
    const v = parseFloat(form.economyIndicator.gdp_perkapita || 0);
    if (v < 35) return 'Rendah';
    if (v < 80) return 'Sedang';
    return 'Tinggi';
});

const gdpGrowthLabel = computed(() => {
    const v = parseFloat(form.economyIndicator.gdp_growth || 0);
    if (v >= 5) return 'Di atas Nasional';
    return 'Di bawah Nasional';
});

const gdpConcentrationLabel = computed(() => {
    const v = gdpConcentration.value;
    if (v > 2500) return 'Tinggi';
    if (v > 1500) return 'Sedang';
    return 'Rendah';
});

const topSectors = computed(() => {
    const list = sectors.map(s => {
        return {
            label: s.label,
            value: parseFloat(form.sectoralGdp[s.key] || 0),
            percentage: sectorPercentages.value[s.key] || 0
        };
    });
    list.sort((a, b) => b.value - a.value);
    return list.slice(0, 3);
});

</script>

<template>

    <Head title="Kondisi Ekonomi Daerah" />

    <AssessmentStepper :assessment-id="assessment.id" :current-step="6" />

    <!-- MAIN CONTENT -->
    <div class="p-6 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-[900px] mx-auto">

            <div class="flex justify-end items-center mb-4">
                <div class="flex items-center gap-4">
                    <span v-show="form.recentlySuccessful"
                        class="text-sm text-green-600 transition-opacity font-medium">Saved.</span>

                    <Dialog v-model:open="isModalOpen">
                        <DialogTrigger as-child v-if="can('create-assessments')">
                            <Button
                                class="bg-teal-600 hover:bg-teal-700 text-white flex items-center gap-2 shadow-sm rounded-full px-6">
                                <Plus :size="16" /> Tambah Data
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Tambah Data Ekonomi</DialogTitle>
                                <DialogDescription>
                                    Pilih tahun untuk memulai entri data ekonomi baru. Form saat ini akan dibersihkan.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="space-y-4 py-4">
                                <div class="space-y-2">
                                    <Label>Tahun</Label>
                                    <Select v-model="modalYear">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih Tahun" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="y in availableYears" :key="y" :value="y.toString()">
                                                {{ y }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button variant="outline" @click="isModalOpen = false">Batal</Button>
                                <Button @click="startNewYear"
                                    class="bg-teal-600 text-white hover:bg-teal-700">Lanjut</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <Button v-if="can('delete-economy_indicators')" variant="destructive" size="sm"
                        class="flex items-center gap-2 shadow-sm rounded-full px-6" @click="isDeleteDialogOpen = true">
                        <Trash :size="16" /> Hapus Data {{ year }}
                    </Button>

                    <!-- Delete Confirmation Dialog -->
                    <Dialog v-model:open="isDeleteDialogOpen">
                        <DialogContent class="max-w-md">
                            <DialogHeader>
                                <DialogTitle>Konfirmasi Hapus</DialogTitle>
                                <DialogDescription>
                                    Apakah Anda yakin ingin menghapus data ekonomi tahun <strong>{{ year }}</strong>?
                                    Data indikator ekonomi dan PDRB sektoral akan dihapus. Tindakan ini tidak dapat
                                    dibatalkan.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <Button variant="outline" @click="isDeleteDialogOpen = false">Batal</Button>
                                <Button variant="destructive" @click="deleteYearData">Hapus</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">

                <div class="bg-teal-600 text-white text-center font-bold py-4 shadow-inner">
                    <div class="flex items-center justify-center gap-2">
                        <h2 class="text-2xl font-bold text-white">Data Ekonomi</h2>
                        <HoverCard>
                            <HoverCardTrigger>
                                <HelpCircle :size="20" class="text-white" />
                            </HoverCardTrigger>
                            <HoverCardContent>
                                Pada Asessment Kondisi Ekonomi, Saudara diminta untuk memasukan data
                                ekonomi seperti
                                tingkat kemiskinan, tingkat pengangguran, dan Indeks Pembangunan Manusia (IPM). Pada
                                bagian ini juga Saudara diminta untuk menyesuaikan Data PDRB ADHB Per Lapangan Usaha
                            </HoverCardContent>
                        </HoverCard>
                    </div>

                    <div class="text-[13px] mt-1 text-teal-100">Kondisi Ekonomi Daerah (berdasarkan data {{ year }})
                    </div>
                </div>

                <div class="p-6">
                    <!-- FIRST TABLE -->
                    <table class="w-full text-[13px] border-collapse mb-8">
                        <tbody>
                            <tr>
                                <td class="w-[35%] font-bold p-1">Pemda</td>
                                <td class="w-[30%] border border-gray-400 p-0 pl-1 text-[#da4118] bg-transparent">
                                    {{ govName }}
                                </td>
                                <td class="w-[35%] p-1"></td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Tingkat Pemerintahan</td>
                                <td class="border border-gray-400 p-0 pl-1 font-bold text-[#f56600] bg-[#f7f0eb]">{{
                                    govLevel }}
                                </td>
                                <td class="p-1"></td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Tingkat Kemiskinan</td>
                                <td class="border border-gray-400 p-0 bg-[#f4f4f4]">
                                    <div class="relative w-full h-full flex items-center justify-end">
                                        <input type="number" step="0.01" v-model="form.economyIndicator.poverty"
                                            class="w-full bg-transparent border-0 text-right pr-4 text-[#da4118] font-bold py-0.5 outline-none focus:ring-1 ring-inset ring-[#5d9b9b]" />
                                        <span
                                            class="absolute right-1 text-[#da4118] font-bold pointer-events-none">%</span>
                                    </div>
                                </td>
                                <td class="p-1 pl-2 text-gray-500 italic">{{ povertyLabel }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Tingkat Pengangguran</td>
                                <td class="border border-gray-400 p-0 bg-[#f4f4f4]">
                                    <div class="relative w-full h-full flex items-center justify-end">
                                        <input type="number" step="0.01" v-model="form.economyIndicator.unemployment"
                                            class="w-full bg-transparent border-0 text-right pr-4 text-[#da4118] font-bold py-0.5 outline-none focus:ring-1 ring-inset ring-[#5d9b9b]" />
                                        <span
                                            class="absolute right-1 text-[#da4118] font-bold pointer-events-none">%</span>
                                    </div>
                                </td>
                                <td class="p-1 pl-2 text-gray-500 italic">{{ unemploymentLabel }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Indeks Pembangunan Manusia</td>
                                <td class="border border-gray-400 p-0 bg-[#f4f4f4]">
                                    <input type="number" step="0.01" v-model="form.economyIndicator.hdci"
                                        class="w-full bg-transparent border-0 text-right text-[#da4118] font-bold py-0.5 outline-none focus:ring-1 ring-inset ring-[#5d9b9b]" />
                                </td>
                                <td class="p-1 pl-2 text-gray-500 italic">{{ hdciLabel }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">PDRB HB per Kapita (Juta Rupiah)</td>
                                <td class="border border-gray-400 p-0 bg-[#f4f4f4]">
                                    <input type="number" step="0.01" v-model="form.economyIndicator.gdp_perkapita"
                                        class="w-full bg-transparent border-0 text-right text-[#da4118] font-bold py-0.5 outline-none focus:ring-1 ring-inset ring-[#5d9b9b]" />
                                </td>
                                <td class="p-1 pl-2 text-gray-500 italic">{{ gdpPerkapitaLabel }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Pertumbuhan PDRB ADHK</td>
                                <td class="border border-gray-400 p-0 bg-[#f4f4f4]">
                                    <div class="relative w-full h-full flex items-center justify-end">
                                        <input type="number" step="0.01" v-model="form.economyIndicator.gdp_growth"
                                            class="w-full bg-transparent border-0 text-right pr-4 text-[#da4118] font-bold py-0.5 outline-none focus:ring-1 ring-inset ring-[#5d9b9b]" />
                                        <span
                                            class="absolute right-1 text-[#da4118] font-bold pointer-events-none">%</span>
                                    </div>
                                </td>
                                <td class="p-1 pl-2 text-gray-500 italic">{{ gdpGrowthLabel }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Tingkat Konsentrasi PDRB</td>
                                <td
                                    class="border border-gray-400 p-1 py-0.5 text-right text-[#da4118] font-bold bg-[#f4f4f4]">
                                    {{ formatNumber(gdpConcentration) }}
                                </td>
                                <td class="p-1 pl-2 text-gray-500 italic">{{ gdpConcentrationLabel }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- SECTORAL GDP TABLE -->
                    <table class="w-full text-[13px] border-collapse mb-8 border border-white">
                        <thead>
                            <tr>
                                <th class="bg-[#5d9b9b] text-white p-1 text-center font-normal border border-white">
                                    Lapangan
                                    Usaha</th>
                                <th
                                    class="bg-[#5d9b9b] text-white p-1 text-center font-normal border border-white w-[30%]">
                                    PDRB
                                    ADHB (Juta Rupiah)</th>
                                <th
                                    class="bg-[#5d9b9b] text-white p-1 text-center font-normal border border-white w-[15%]">
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sector in sectors" :key="sector.key">
                                <td class="p-1">{{ sector.label }}</td>
                                <td class="p-0 border-x border-[#5d9b9b]">
                                    <!-- Input -->
                                    <input type="text" :value="formatCurrency(form.sectoralGdp[sector.key])"
                                        @focus="handleCurrencyFocus" @input="handleSectorInput($event, sector.key)"
                                        @blur="handleSectorBlur($event, sector.key)"
                                        class="w-full bg-transparent border-0 text-right py-0.5 px-1 outline-none focus:ring-2 ring-inset ring-green-600 focus:bg-[#b0d9ac] hover:bg-gray-100 transition-colors m-0 rounded-none shadow-none" />
                                </td>
                                <td class="p-1 text-right bg-[#e2f1d2]">
                                    {{ formatNumber(sectorPercentages[sector.key] || 0) }}%
                                </td>
                            </tr>
                            <tr class="bg-[#c0c0c0] font-bold">
                                <td class="p-1">Total</td>
                                <td class="p-1 text-right border border-[#5d9b9b]">{{ formatNumber(totalGdp) }}</td>
                                <td class="p-1 text-right border border-white">{{ formatNumber(Object.values(sectorPercentages).reduce((acc, val) => acc + val, 0)) }}%</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- COMPARISON SECTION -->
                    <div class="w-full bg-[#5d9b9b] text-black text-center font-bold text-xl py-2 mb-4">
                        <div class="text-lg text-white">Perbandingan dengan {{ govLevel === 'Provinsi' ? 'Provinsi' :
                            'Kabupaten/Kota' }} Lainnya</div>
                        <div class="text-[13px] mt-1 text-white">Kondisi Ekonomi Daerah tahun {{ year }}</div>
                    </div>

                    <table class="w-full text-[13px] border-collapse mb-4">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-center p-1 font-bold">Minimum</th>
                                <th class="text-center p-1 font-bold">Rata-rata</th>
                                <th class="text-center p-1 font-bold">Maksimum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-bold p-1">Tingkat Kemiskinan</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.poverty.min) }}%</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.poverty.avg) }}%</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.poverty.max) }}%</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Tingkat Pengangguran</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.unemployment.min) }}%</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.unemployment.avg) }}%</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.unemployment.max) }}%</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Indeks Pembangunan Manusia</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.hdci.min) }}</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.hdci.avg) }}</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.hdci.max) }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">PDRB HB per Kapita (Juta Rupiah)</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.gdp_perkapita.min) }}</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.gdp_perkapita.avg) }}</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.gdp_perkapita.max) }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold p-1">Pertumbuhan PDRB ADHK</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.gdp_growth.min) }}%</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.gdp_growth.avg) }}%</td>
                                <td class="text-right p-1">{{ formatNumber(comparison.gdp_growth.max) }}%</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-6 flex text-[13px]">
                        <div class="font-bold w-[55%] p-1 pb-2">Sektor Prioritas</div>
                        <div class="font-bold w-[30%] p-1 pb-2 text-right">PDRB ADHB (Juta Rupiah)</div>
                        <div class="font-bold w-[15%] p-1 pb-2 text-right">%</div>
                    </div>

                    <div class="flex text-[13px]" v-for="(topSect, index) in topSectors" :key="index">
                        <div class="w-[55%] pl-2 py-0.5 flex gap-2">
                            <span class="font-bold">{{ index + 1 }}</span>
                            <span>{{ topSect.label }}</span>
                        </div>
                        <div class="w-[30%] py-0.5 px-1 text-right">{{ formatNumber(topSect.value) }}</div>
                        <div class="w-[15%] py-0.5 px-1 text-right">{{ formatNumber(topSect.percentage) }}%</div>
                    </div>

                </div>

                <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-8">
                    <Link href="/assessments">
                        <Button type="button" variant="outline" class="flex items-center gap-2">
                            <ChevronLeft :size="16" /> Back
                        </Button>
                    </Link>

                    <div v-if="can('create-assessments')">
                        <Button @click="submit" :disabled="form.processing"
                            class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
                            <Save :size="16" /> Simpan dan Lanjutkan
                        </Button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>
