'use client';

import ErrorState from '@/components/ErrorState';

export default function Error({ reset }: { error: globalThis.Error; reset: () => void }) {
    return <ErrorState reset={reset} />;
}
