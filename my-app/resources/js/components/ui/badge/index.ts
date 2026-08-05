import { cva, type VariantProps } from 'class-variance-authority';

export { default as Badge } from './Badge.vue';

export const badgeVariants = cva('inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2', {
    variants: {
        variant: {
            default: 'border-transparent bg-primary text-primary-foreground',
            secondary: 'border-transparent bg-secondary text-secondary-foreground',
            destructive: 'border-transparent bg-destructive text-destructive-foreground',
            outline: 'border-input text-foreground',
            success: 'border-transparent bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-400',
            warning: 'border-transparent bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-400',
            info: 'border-transparent bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-400',
        },
    },
    defaultVariants: {
        variant: 'default',
    },
});

export type BadgeVariants = VariantProps<typeof badgeVariants>;
