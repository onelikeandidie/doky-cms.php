<x-app-layout :showNavigation="false">
    <div
        class="tw-absolute tw-bg-radient-circle-c tw-top-0 tw-right-0 tw-left-0 tw-bottom-0 -tw-z-10 tw-from-neutral-900 tw-from-90% tw-to-indigo-300 tw-bg-[length:20px_20px]">

    </div>
    <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen">
        <form method="POST" action="{{ route('login') }}"
              class="tw-p-4 tw-rounded tw-shadow tw-bg-white tw-w-64">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email"
                       class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 dark:tw-text-gray-400">{{ __('Email') }}</label>
                <input id="email" class="tw-py-1 tw-px-2 tw-border tw-rounded" type="email" name="email"
                       :value="old('email')" required autofocus autocomplete="username"/>
                @error('email')
                <span class="tw-text-red-600 tw-text-lg">
                            {{ $message }}
                        </span>
                @enderror
            </div>

            <!-- Password -->
            <div class="tw-mt-4">
                <label for="password"
                       class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 dark:tw-text-gray-400">{{ __('Password') }}</label>
                <input id="password" class="tw-py-1 tw-px-2 tw-border tw-rounded" type="password" name="password"
                       required autocomplete="current-password"/>
                @error('password')
                <span class="tw-text-red-600 tw-text-lg">
                            {{ $message }}
                        </span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="tw-block tw-mt-4">
                <label for="remember_me" class="tw-inline-flex tw-items-center">
                    <input id="remember_me" type="checkbox"
                           class="tw-rounded dark:tw-bg-gray-900 tw-border-gray-300 dark:tw-border-gray-700 tw-text-indigo-600 tw-shadow-sm focus:tw-ring-indigo-500 dark:focus:tw-ring-indigo-600 dark:focus:tw-ring-offset-gray-800"
                           name="remember">
                    <span
                        class="tw-ms-2 tw-text-sm tw-text-gray-600 dark:tw-text-gray-400">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="tw-flex tw-flex-col tw-items-center tw-justify-end tw-mt-4">
                @if (Route::has('password.request'))
                    <a class="tw-underline tw-text-sm tw-text-gray-600 dark:tw-text-gray-400 hover:tw-text-gray-900 dark:hover:tw-text-gray-100 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500 dark:focus:tw-ring-offset-gray-800"
                       href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <button
                    class="tw-px-2 tw-py-1 tw-rounded tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50">
                    {{ __('Log in') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
