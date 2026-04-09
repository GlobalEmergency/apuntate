import { NavItem } from './nav-item/nav-item';

export const navItems: NavItem[] = [
  {
    navCap: 'Home',
  },
  {
    displayName: 'Dashboard',
    iconName: 'layout-dashboard',
    route: '/dashboard',
  },
  {
    navCap: 'Servicios',
  },
  {
    displayName: 'Calendario',
    iconName: 'calendar',
    route: '/calendar',
  },
  {
    displayName: 'Nuevo',
    iconName: 'plus',
    route: '/service/add',
    admin: true,
  },
  {
    navCap: 'Administración',
    admin: true,
  },
  {
    displayName: 'Unidades',
    iconName: 'truck',
    route: '/admin/units',
    admin: true,
  },
  {
    displayName: 'Roles',
    iconName: 'users',
    route: '/admin/components',
    admin: true,
  },
  {
    displayName: 'Cualificaciones',
    iconName: 'certificate',
    route: '/admin/requirements',
    admin: true,
  },
  {
    displayName: 'Especialidades',
    iconName: 'star',
    route: '/admin/specialities',
    admin: true,
  },
  {
    displayName: 'Miembros',
    iconName: 'users-group',
    route: '/admin/members',
    admin: true,
  },
  {
    navCap: 'Mi cuenta',
  },
  {
    displayName: 'Mi perfil',
    iconName: 'user',
    route: '/profile',
  },
];
