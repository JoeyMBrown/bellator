import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

const FlashMessages = () => {
    const { props } = usePage<PageProps>();
    const flash = props.flash ?? { success: null, error: null };

    if (!flash.success && !flash.error) return null;

    return (
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {flash.success && (
                <div
                    role="status"
                    className="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-200"
                >
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div
                    role="alert"
                    className="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:text-red-200"
                >
                    {flash.error}
                </div>
            )}
        </div>
    );
};

export default FlashMessages;
