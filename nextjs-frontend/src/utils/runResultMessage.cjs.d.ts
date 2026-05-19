export function getRunResultNotice(data?: {
  completed?: number;
  failed?: number;
}): {
  type: 'success' | 'warning' | 'error';
  text: string;
};
