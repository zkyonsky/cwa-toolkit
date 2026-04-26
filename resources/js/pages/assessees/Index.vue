<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Pencil, Trash, Plus, Rocket } from '@lucide/vue';
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
import { can } from '@/lib/can'

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Assessees',
        href: '/assessees',
      },
    ],
  },
});

defineProps({
  assessees: Array<{ id: number, gov_id: number, name: string, position: string, contact: string, 
                     gov?: { id: number, name: string },
                     user?: { id: number, name: string } }>
});

const page = usePage<any>();
const handleDelete = (id: number) => {
  if (confirm("Are you sure you want to delete this assessee?")) {
    router.delete(`/assessees/${id}`);
  }
}
</script>

<template>
  <Head title="Assessees" />

  <Alert class="m-2 bg-gray-100" v-if="page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <Link
   v-if="can('create-assessees')"
   href="/assessees/create" class="m-2 w-max px-3 py-2 bg-primary text-xs text-white rounded-full inline-block">
    <div class="flex items-center gap-2">
      <Plus :size="16" /> Assessee
    </div>
  </Link>

  <Table class="m-2">
    <TableCaption>A list of assessees.</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead>Government</TableHead>
        <TableHead>Name</TableHead>
        <TableHead>Position</TableHead>
        <TableHead>Contact</TableHead>
        <TableHead class="text-right">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="assessee in assessees" :key="assessee.id">
        <TableCell>{{ assessee.gov?.name }}</TableCell>
        <TableCell class="font-medium">{{ assessee.user?.name }}</TableCell>
        <TableCell>{{ assessee.position }}</TableCell>
        <TableCell>{{ assessee.contact }}</TableCell>
        <TableCell class="text-right">
          <Link 
          v-if="can('edit-assessees')"
          :href="`/assessees/${assessee.id}/edit`">
            <Button class="mr-2 size-6">
              <Pencil />
            </Button>
          </Link>
          <Button 
          v-if="can('delete-assessees')"
          variant="destructive" class="size-6" @click="handleDelete(assessee.id)">
            <Trash />
          </Button>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
