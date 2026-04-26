<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Pencil, Trash, UserPlus, Rocket } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
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
        title: 'Roles',
        href: '/roles',
      },
    ],
  },
});

defineProps({
  roles: Array<{ id: number, name: string, permissions: Array<{ name: string }> }>
});

const page = usePage();
const handleDelete = (id: number) => {
  if (confirm("Are you sure you want to delete this role?")) {
    router.delete(`/roles/${id}`);
  }
}

</script>

<template>

  <Head title="Roles" />

  <Alert class="m-2 bg-gray-100" v-if="page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <Link href="/roles/create" class="m-2 w-20 px-3 py-2 bg-blue-500 text-xs text-white rounded-full">
    <div class="flex items-center gap-2">
      <UserPlus :size="16" /> Role
    </div>
  </Link>

  <div class="mx-auto my-4 px-4 w-full lg:w-[80%]">
    <Table class="table-fixed">
      <TableCaption>A list of roles.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead>
            Name
          </TableHead>
          <TableHead>
            Permissions
          </TableHead>
          <TableHead class="text-right">
            Actions
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="role in roles" :key="role.id">
          <TableCell class="whitespace-normal">
            {{ role.name }}
          </TableCell>
          <TableCell class="whitespace-normal">
            <Badge class="m-1" variant="destructive" v-for="permission in role.permissions">
              {{ permission.name }}
            </Badge>
          </TableCell>
          <TableCell class="text-right">
            <Link 
            v-if="can('edit-roles')"
            :href="`/roles/${role.id}/edit`">
              <Button class="mr-2 size-6">
                <Pencil />
              </Button>
            </Link>
            <Button 
            v-if="can('delete-roles')"
            variant="destructive" class="size-6" @click="handleDelete(role.id)">
              <Trash />
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
