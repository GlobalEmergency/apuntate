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
];
