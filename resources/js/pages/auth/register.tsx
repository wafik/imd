import { Head, useForm } from '@inertiajs/react';
import { Eye, EyeOff, LoaderCircle, Lock, Mail, Phone, User, UserCheck } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import { toast } from 'sonner';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    name: string;
    username: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        name: '',
        username: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
            onSuccess: () => {
                toast.success('Account created successfully! Welcome!');
            },
        });
    };

    return (
        <AuthLayout title="Create Account" description="Join us today and start managing IMD data efficiently">
            <Head title="Register" />

            <form className="space-y-6" onSubmit={submit}>
                <div className="space-y-5">
                    {/* Name Field */}
                    <div className="space-y-2">
                        <Label htmlFor="name" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Full Name
                        </Label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <User className="h-5 w-5 text-gray-400" />
                            </div>
                            <Input
                                id="name"
                                type="text"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                disabled={processing}
                                placeholder="Enter your full name"
                                className="h-12 rounded-lg border-gray-300 pl-10 transition-colors duration-200 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600"
                            />
                        </div>
                        <InputError message={errors.name} />
                    </div>

                    {/* Username Field */}
                    <div className="space-y-2">
                        <Label htmlFor="username" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Username
                        </Label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <UserCheck className="h-5 w-5 text-gray-400" />
                            </div>
                            <Input
                                id="username"
                                type="text"
                                required
                                tabIndex={2}
                                autoComplete="username"
                                value={data.username}
                                onChange={(e) => setData('username', e.target.value)}
                                disabled={processing}
                                placeholder="Choose a unique username"
                                className="h-12 rounded-lg border-gray-300 pl-10 transition-colors duration-200 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600"
                            />
                        </div>
                        <InputError message={errors.username} />
                    </div>

                    {/* Email Field */}
                    <div className="space-y-2">
                        <Label htmlFor="email" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email Address
                        </Label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Mail className="h-5 w-5 text-gray-400" />
                            </div>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabIndex={3}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                disabled={processing}
                                placeholder="Enter your email address"
                                className="h-12 rounded-lg border-gray-300 pl-10 transition-colors duration-200 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600"
                            />
                        </div>
                        <InputError message={errors.email} />
                    </div>

                    {/* Phone Field */}
                    <div className="space-y-2">
                        <Label htmlFor="phone" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Phone Number
                        </Label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Phone className="h-5 w-5 text-gray-400" />
                            </div>
                            <Input
                                id="phone"
                                type="tel"
                                required
                                tabIndex={4}
                                autoComplete="tel"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                disabled={processing}
                                placeholder="Enter your phone number"
                                className="h-12 rounded-lg border-gray-300 pl-10 transition-colors duration-200 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600"
                            />
                        </div>
                        <InputError message={errors.phone} />
                    </div>

                    {/* Password Field */}
                    <div className="space-y-2">
                        <Label htmlFor="password" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </Label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Lock className="h-5 w-5 text-gray-400" />
                            </div>
                            <Input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                required
                                tabIndex={5}
                                autoComplete="new-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                disabled={processing}
                                placeholder="Create a strong password"
                                className="h-12 rounded-lg border-gray-300 pr-10 pl-10 transition-colors duration-200 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600"
                            />
                            <button
                                type="button"
                                className="absolute inset-y-0 right-0 flex items-center pr-3"
                                onClick={() => setShowPassword(!showPassword)}
                                tabIndex={-1}
                            >
                                {showPassword ? (
                                    <EyeOff className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                ) : (
                                    <Eye className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                )}
                            </button>
                        </div>
                        <InputError message={errors.password} />
                    </div>

                    {/* Confirm Password Field */}
                    <div className="space-y-2">
                        <Label htmlFor="password_confirmation" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Confirm Password
                        </Label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Lock className="h-5 w-5 text-gray-400" />
                            </div>
                            <Input
                                id="password_confirmation"
                                type={showConfirmPassword ? 'text' : 'password'}
                                required
                                tabIndex={6}
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                disabled={processing}
                                placeholder="Confirm your password"
                                className="h-12 rounded-lg border-gray-300 pr-10 pl-10 transition-colors duration-200 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600"
                            />
                            <button
                                type="button"
                                className="absolute inset-y-0 right-0 flex items-center pr-3"
                                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                tabIndex={-1}
                            >
                                {showConfirmPassword ? (
                                    <EyeOff className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                ) : (
                                    <Eye className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                )}
                            </button>
                        </div>
                        <InputError message={errors.password_confirmation} />
                    </div>

                    {/* Submit Button */}
                    <Button
                        type="submit"
                        className="h-12 w-full transform rounded-lg bg-gradient-to-r from-blue-600 to-cyan-600 font-medium text-white shadow-lg transition-all duration-200 hover:scale-[1.02] hover:from-blue-700 hover:to-cyan-700 hover:shadow-xl"
                        tabIndex={7}
                        disabled={processing}
                    >
                        {processing ? (
                            <>
                                <LoaderCircle className="mr-2 h-5 w-5 animate-spin" />
                                Creating account...
                            </>
                        ) : (
                            <>
                                <UserCheck className="mr-2 h-5 w-5" />
                                Create Account
                            </>
                        )}
                    </Button>
                </div>

                {/* Sign In Link */}
                <div className="border-t border-gray-200 pt-4 text-center dark:border-gray-700">
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                        Already have an account?{' '}
                        <TextLink
                            href={route('login')}
                            className="font-medium text-blue-600 transition-colors duration-200 hover:text-blue-500 dark:text-blue-400"
                            tabIndex={8}
                        >
                            Sign in here
                        </TextLink>
                    </p>
                </div>
            </form>
        </AuthLayout>
    );
}
