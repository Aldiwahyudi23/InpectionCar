import { usePage } from '@inertiajs/vue3';

export function useAuth() {
  const page = usePage();

  const roles = page.props.global?.roles || [];
  const permissions = page.props.global?.permissions || [];

  function hasRole(...checkRoles) {
    return roles.some(r => checkRoles.includes(r));
  }

  function can(permission) {
    return permissions.includes(permission);
  }

  function canRolePermission(rolesCheck = [], permission = null) {
    const hasRoleMatch = rolesCheck.length === 0 || hasRole(...rolesCheck);

    if (!permission) {
      return hasRoleMatch;
    }

    return hasRoleMatch && can(permission);
  }

  return { hasRole, can, canRolePermission };
}
