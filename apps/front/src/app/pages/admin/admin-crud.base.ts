export type FeedbackMessage = { text: string; type: 'success' | 'error' } | null;

export abstract class AdminCrudBase {
  loading = true;
  saving = false;
  message: FeedbackMessage = null;
  showForm = false;

  abstract loadAll(): void;

  protected onSuccess(msg: string): void {
    this.saving = false;
    this.showForm = false;
    this.message = { text: msg, type: 'success' };
    this.loadAll();
  }

  protected onError(err: { error?: { error?: string } }): void {
    this.saving = false;
    this.message = { text: err.error?.error || 'An error occurred.', type: 'error' };
  }
}
