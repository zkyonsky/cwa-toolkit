import { usePage } from "@inertiajs/vue3";

export function can(permission: string | undefined): boolean {
    if (!permission) return true;
    const page = usePage();
    return page.props.auth.permissions.includes(permission);
}

export function canAny(permissions: string[]): boolean {
    const page = usePage();
    return permissions.some((permission) => page.props.auth.permissions.includes(permission));
}

export function canAll(permissions: string[]): boolean {
    const page = usePage();
    return permissions.every((permission) => page.props.auth.permissions.includes(permission));
}