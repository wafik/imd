// Removed AppLogoIcon import; using external logo.svg instead
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({ children, title, description }: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-cyan-50 dark:from-gray-900 dark:via-gray-800 dark:to-blue-900">
            <div className="flex min-h-screen items-center justify-center p-4">
                <div className="w-full max-w-md">
                    <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
                        <div className="px-8 py-10">
                            <div className="flex flex-col items-center space-y-6">
                                {/* Logo Section */}
                                <Link
                                    href={route('home')}
                                    className="group flex flex-col items-center transition-transform duration-300 hover:scale-105"
                                >
                                    <div className="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg">
                                        <img src="/logo.svg" alt="IMD Logo" className="h-12 w-12 brightness-0 invert filter" />
                                    </div>
                                    <div className="text-center">
                                        <h2 className="bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-2xl font-bold text-transparent">
                                            IMD System
                                        </h2>
                                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">Inisiasi Menyusu Dini</p>
                                    </div>
                                </Link>

                                {/* Title Section */}
                                <div className="space-y-2 text-center">
                                    <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">{title}</h1>
                                    <p className="text-sm leading-relaxed text-gray-600 dark:text-gray-300">{description}</p>
                                </div>
                            </div>

                            {/* Form Content */}
                            <div className="mt-8">{children}</div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="mt-8 text-center">
                        <p className="text-sm text-gray-500 dark:text-gray-400">© 2025 IMD Management System. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
