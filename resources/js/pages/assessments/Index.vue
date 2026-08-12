<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Database, Trash, Plus, Rocket, Building, Banknote, LineChart, CreditCard, Calculator, BarChart, Pencil, Star, ClipboardList, ListChecks } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { can } from '@/lib/can'
import { computed } from 'vue';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Assessments',
        href: '/assessments',
      },
    ],
  },
});

const props = defineProps<{
  assessments: Array<{
    id: number,
    assessee_id: number,
    date: string,
    info: string,
    result: string,
    economy_condition?: number | null,
    financial_condition?: number | null,
    final_rating?: string | null,
    assessee?: { id: number, name: string, gov?: { name: string } }
  }>,
  hasAssessee: boolean
}>();

const page = usePage<any>();

const isAdmin = computed(() => {
  const roles = page.props.auth.roles || [];
  return roles.includes('Super Admin') || roles.includes('Admin');
})

const isUser = computed(() => {
  const roles = page.props.auth.roles || [];
  return roles.includes('User');
})

// Blank page if user role but no assessee
const showPage = computed(() => {
  if (isAdmin.value) return true;
  if (isUser.value) return props.hasAssessee;
  return true; // Default to showing (maybe they have other roles?)
})

const handleDelete = (id: number) => {
  if (confirm("Are you sure you want to delete this assessment?")) {
    router.delete(`/assessments/${id}`);
  }
}
</script>

<template>

  <Head title="Assessments" />

  <template v-if="showPage">
    <Alert class="m-2 bg-gray-100" v-if="page.props.flash?.message">
      <Rocket :size="16" />
      <AlertTitle>Notification</AlertTitle>
      <AlertDescription>
        {{ page.props.flash.message }}
      </AlertDescription>
    </Alert>
    <div v-if="can('create-assessments')">
      <Link href="/assessments/create"
        class="m-2 w-max px-3 py-2 bg-primary text-xs text-white rounded-full inline-block">
        <div class="flex items-center gap-2">
          <Plus :size="16" /> Assessment
        </div>
      </Link>
    </div>

    <!-- Admin Table View -->
    <Table class="m-2" v-if="isAdmin">
      <TableCaption>A list of assessments.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead>Pemda</TableHead>
          <TableHead>Tanggal</TableHead>
          <TableHead>Kondisi Ekonomi Daerah</TableHead>
          <TableHead>Kondisi Keuangan Daerah</TableHead>
          <TableHead>Rating Final</TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="assessment in assessments" :key="assessment.id">
          <TableCell>{{ assessment.assessee?.gov?.name }}</TableCell>
          <TableCell>{{ assessment.date }}</TableCell>
          <TableCell>{{ assessment.economy_condition ?? '-' }}</TableCell>
          <TableCell>{{ assessment.financial_condition ?? '-' }}</TableCell>
          <TableCell>{{ assessment.final_rating ?? '-' }}</TableCell>
          <TableCell class="text-right">
            <Link v-if="can('edit-assessments')" :href="`/assessments/${assessment.id}/edit`">
              <Button class="mr-2 size-6">
                <Pencil />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/infrastructure`">
              <Button class="mr-2 size-6 bg-teal-600 hover:bg-teal-700" title="Edit Infrastructure">
                <Building />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/budget-real`">
              <Button class="mr-2 size-6 bg-green-600 hover:bg-green-700" title="Edit Data Keuangan (Budget Real)">
                <Banknote />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/economy-condition`">
              <Button class="mr-2 size-6 bg-blue-600 hover:bg-blue-700" title="Edit Kondisi Ekonomi Daerah">
                <LineChart />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/financial-condition`">
              <Button class="mr-2 size-6 bg-indigo-600 hover:bg-indigo-700" title="Edit Kondisi Keuangan Daerah">
                <BarChart />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/debt-service`">
              <Button class="mr-2 size-6 bg-purple-600 hover:bg-purple-700"
                title="Edit Debt Service Coverage Ratio (DSCR)">
                <CreditCard />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/dscr`">
              <Button class="mr-2 size-6 bg-orange-600 hover:bg-orange-700" title="Kesimpulan DSCR">
                <Calculator />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/indicative-rating`">
              <Button class="mr-2 size-6 bg-amber-500 hover:bg-amber-600" title="Indikasi Rating Pemda">
                <Star />
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/action-plan`">
              <Button class="mr-2 size-6 bg-rose-600 hover:bg-rose-700" title="Rencana Aksi">
                <ClipboardList />
              </Button>
            </Link>
            <Button v-if="can('delete-assessments')" variant="destructive" class="size-6"
              @click="handleDelete(assessment.id)">
              <Trash />
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <!-- User Card View -->
    <div v-else-if="isUser" class="m-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card v-for="assessment in assessments" :key="assessment.id" class="overflow-hidden">
        <CardHeader class="pb-2">
          <CardTitle class="text-lg">{{ assessment.assessee?.gov?.name }}</CardTitle>
          <CardDescription>{{ assessment.date }}</CardDescription>
        </CardHeader>
        <CardContent class="grid gap-2">
          <div class="flex flex-wrap gap-2">
            <Link v-if="can('edit-assessments')" :href="`/assessments/${assessment.id}/edit`" class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-purple-50 hover:bg-purple-100 border-purple-200 text-purple-700">
                <Database class="mr-1 size-3" /> Data Pemda
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/infrastructure`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-teal-50 hover:bg-teal-100 border-teal-200 text-teal-700">
                <Building class="mr-1 size-3" /> Infrastruktur
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/budget-real`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-green-50 hover:bg-green-100 border-green-200 text-green-700">
                <Banknote class="mr-1 size-3" /> Realisasi APBD
              </Button>
            </Link>
          </div>
          <div class="flex flex-wrap gap-2">
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/financial-condition`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-indigo-50 hover:bg-indigo-100 border-indigo-200 text-indigo-700">
                <BarChart class="mr-1 size-3" /> Kondisi Keuangan
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/economy-condition`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-blue-50 hover:bg-blue-100 border-blue-200 text-blue-700">
                <LineChart class="mr-1 size-3" /> Kondisi Ekonomi
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/dscr`" class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-orange-50 hover:bg-orange-100 border-orange-200 text-orange-700">
                <Calculator class="mr-1 size-3" /> DSCR
              </Button>
            </Link>
          </div>
          <div class="flex flex-wrap gap-2">
            <!-- <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/debt-service`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-purple-50 hover:bg-purple-100 border-purple-200 text-purple-700">
                <CreditCard class="mr-1 size-3" /> Debt Service
              </Button>
            </Link> -->
            
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/indicative-rating`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-amber-50 hover:bg-amber-100 border-amber-200 text-amber-700">
                <Star class="mr-1 size-3" /> Rating
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/action-plan`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-rose-50 hover:bg-rose-100 border-rose-200 text-rose-700">
                <ClipboardList class="mr-1 size-3" /> Rencana Aksi
              </Button>
            </Link>
            <Link v-if="can('edit-assessments')" :href="`/assessment-details/${assessment.id}/report`"
              class="flex-1">
              <Button variant="outline"
                class="w-full justify-start text-xs bg-teal-50 hover:bg-teal-100 border-teal-200 text-teal-700">
                <ListChecks class="mr-1 size-3" /> Laporan
              </Button>
            </Link>
          </div>
        </CardContent>
        <CardFooter class="flex gap-2 pt-2 border-t mt-auto">
          <Button v-if="can('delete-assessments')" variant="destructive" size="icon-sm"
            @click="handleDelete(assessment.id)">
            <Trash :size="14" />
          </Button>
        </CardFooter>
      </Card>
    </div>
  </template>
</template>
