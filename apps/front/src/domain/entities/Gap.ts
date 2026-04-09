export interface Gap {
  id: string;
  component?: string;
  unit?: string;
  quantity?: number;
  requirements?: { id: string; name: string }[];
  user?: { id: string; name: string; surname: string } | null;
}
