<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Rocket, Download, Upload } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const datasets = [
    { title: 'Realisasi APBD', type: 'budget_reals', description: 'Data master realisasi anggaran daerah.' },
    { title: 'Rencana APBD', type: 'budget_plans', description: 'Data master rencana anggaran daerah.' },
    { title: 'Indikator Ekonomi', type: 'economy_indicators', description: 'Data master indikator ekonomi daerah.' },
    { title: 'PDRB Sektoral', type: 'sectoral_gdps', description: 'Data master PDRB sektoral daerah.' },
];

const forms = {
    budget_reals: useForm({ csv_file: null as File | null }),
    budget_plans: useForm({ csv_file: null as File | null }),
    economy_indicators: useForm({ csv_file: null as File | null }),
    sectoral_gdps: useForm({ csv_file: null as File | null }),
};

const handleUpload = (type: 'budget_reals' | 'budget_plans' | 'economy_indicators' | 'sectoral_gdps') => {
    forms[type].post(`/upload-data/${type}`, {
        preserveScroll: true,
        onSuccess: () => {
            forms[type].reset('csv_file');
        }
    });
};
</script>

<template>
    <Head title="Upload Data" />
    <div class="p-6 bg-gray-50 min-h-screen font-sans text-sm">
        <div class="max-w-4xl mx-auto">
            <Alert class="mb-6 bg-white" v-if="$page.props.flash?.message">
                <Rocket class="size-4" />
                <AlertTitle>Notification</AlertTitle>
                <AlertDescription>{{ $page.props.flash.message }}</AlertDescription>
            </Alert>
            <Alert class="mb-6 bg-red-50 text-red-800" v-if="$page.props.flash?.error">
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>{{ $page.props.flash.error }}</AlertDescription>
            </Alert>

            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
                <div class="bg-teal-600 text-white text-center font-bold text-xl py-4 shadow-inner">
                    Upload Data Master
                </div>
                <div class="p-6 space-y-8">
                    <div v-for="dataset in datasets" :key="dataset.type" class="border rounded-lg p-6 bg-gray-50/50 shadow-sm">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-teal-800">{{ dataset.title }}</h3>
                                <p class="text-muted-foreground text-xs mt-1">{{ dataset.description }}</p>
                            </div>
                            <a :href="`/upload-data/template/${dataset.type}`" target="_blank">
                                <Button variant="outline" class="flex items-center gap-2 border-teal-200 text-teal-700 hover:bg-teal-50">
                                    <Download class="size-4" /> Download Template
                                </Button>
                            </a>
                        </div>
                        <form @submit.prevent="handleUpload(dataset.type as any)" class="flex gap-4 items-end">
                            <div class="flex-1 space-y-2">
                                <Label>Upload File CSV</Label>
                                <Input type="file" accept=".csv" @input="forms[dataset.type as keyof typeof forms].csv_file = ($event.target as HTMLInputElement).files?.[0] || null" class="bg-white cursor-pointer" />
                                <div v-if="forms[dataset.type as keyof typeof forms].errors.csv_file" class="text-red-500 text-xs mt-1">
                                    {{ forms[dataset.type as keyof typeof forms].errors.csv_file }}
                                </div>
                            </div>
                            <Button type="submit" :disabled="forms[dataset.type as keyof typeof forms].processing || !forms[dataset.type as keyof typeof forms].csv_file" class="bg-teal-600 hover:bg-teal-700 text-white flex items-center gap-2">
                                <Upload class="size-4" /> Upload
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
