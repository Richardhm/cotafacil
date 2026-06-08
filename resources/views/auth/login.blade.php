<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    @if ($errors->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Erro!</strong>
            <span class="block sm:inline">{{ $errors->first('error') }}</span>
            <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Fechar</title>
                    <path d="M14.348 14.849a1 1 0 010-1.415L16.243 11l-1.895-1.434a1 1 0 111.416-1.416L17.656 9.585l1.434-1.895a1 1 0 111.416 1.416L19.415 11l1.895 1.434a1 1 0 11-1.416 1.416L17.656 12.415l-1.434 1.895a1 1 0 01-1.416 0l-1.433-1.894z" />
                </svg>
            </button>
        </div>
    @endif
    @if ($errors->has('compartilhado'))
        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg flex items-start gap-3" role="alert">
            <svg class="w-5 h-5 text-yellow-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span class="text-sm font-medium">{{ $errors->first('compartilhado') }}</span>
        </div>
    @endif
    @if ($errors->has('message'))
        <div class="bg-green-200 border border-white text-black px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Sucesso!</strong>
            <span class="block sm:inline">{{ $errors->first('message') }}</span>
            <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Fechar</title>
                    <path d="M14.348 14.849a1 1 0 010-1.415L16.243 11l-1.895-1.434a1 1 0 111.416-1.416L17.656 9.585l1.434-1.895a1 1 0 111.416 1.416L19.415 11l1.895 1.434a1 1 0 11-1.416 1.416L17.656 12.415l-1.434 1.895a1 1 0 01-1.416 0l-1.433-1.894z" />
                </svg>
            </button>
        </div>
    @endif
    <section class="rounded-lg w-full min-h-screen flex items-center justify-center flex-col my-auto p-4">
        <img src="{{asset('cotafacil_logo.png')}}" class="mx-auto my-2 h-20 p-1" alt="">
        <form method="POST" action="{{ route('login') }}" id="loginForm" class="p-4 rounded-lg w-full max-w-sm bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
            @csrf
            <input type="hidden" name="device_uuid" id="device_uuid">
            <input type="hidden" name="timezone" id="timezone">
            <input type="hidden" name="screen_resolution" id="screen_resolution">
            <input type="hidden" name="canvas_hash" id="canvas_hash">
            <input type="hidden" name="gpu_renderer" id="gpu_renderer">
            <input type="hidden" name="cpu_cores" id="cpu_cores">
            <input type="hidden" name="device_memory" id="device_memory">
            <div class="mb-4">
                <label for="email" class="block mb-1 font-medium text-white text-sm sm:text-base">Email</label>
                <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-2.5 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div class="mb-4">
                <label for="password" class="block mb-1 font-medium text-white text-sm sm:text-base">Senha</label>
                <div class="relative">
                    <input type="password" name="password" id="password" style="color:black;" class="bg-gray-50 border text-gray-950 border-gray-300 text-sm block w-full p-2.5 focus:border-transparent focus:ring-0 focus:outline-none rounded-lg" required />
                    <button type="button" id="togglePassword" class="absolute right-2 top-2 cursor-pointer" style="color:black;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" id="showIcon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 hidden" id="hideIcon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="flex justify-between">
                <div>
                    <input type="checkbox" name="remember_me" value="1" id="remember_me" class="text-gray-950">
                    <label for="remember_me" class="text-sm dark:text-white text-white cursor-pointer">Lembrar-me</label>
                </div>
                <div>
                    <a href="{{route('password.request')}}" style="text-decoration: underline;" class="text-sm dark:text-white text-white">Esqueceu a Senha?</a>
                </div>
            </div>
            <div class="mx-auto my-5 w-full flex justify-center">
                <button type="submit" style="background-color: rgb(9, 116, 122);color:white;margin:0 auto;" class="focus:outline-none font-medium mt-2 text-lg w-2/3 px-5 py-2 text-center focus:border-transparent focus:ring-0 focus:outline-none rounded-lg text-gray-950">LOGIN</button>
            </div>
        </form>

        <script>
            // Captura dados do dispositivo antes do submit (invisível ao usuário)
            (function () {
                // UUID persistente por dispositivo/navegador
                var deviceUuid = localStorage.getItem('cf_device_uuid');
                if (!deviceUuid) {
                    deviceUuid = crypto.randomUUID
                        ? crypto.randomUUID()
                        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                            var r = Math.random() * 16 | 0;
                            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                        });
                    localStorage.setItem('cf_device_uuid', deviceUuid);
                }
                document.getElementById('device_uuid').value = deviceUuid;

                document.getElementById('timezone').value =
                    Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                document.getElementById('screen_resolution').value =
                    screen.width + 'x' + screen.height;
                document.getElementById('cpu_cores').value =
                    navigator.hardwareConcurrency || '';
                document.getElementById('device_memory').value =
                    navigator.deviceMemory || '';

                // Canvas fingerprint — hash dos pixels renderizados pela GPU
                try {
                    var cv = document.createElement('canvas');
                    var ctx = cv.getContext('2d');
                    cv.width = 240; cv.height = 60;
                    ctx.fillStyle = '#f05';
                    ctx.fillRect(10, 10, 100, 30);
                    ctx.fillStyle = 'rgba(0,200,100,0.8)';
                    ctx.font = 'bold 16px sans-serif';
                    ctx.fillText('cf2026☃é', 20, 35);
                    ctx.strokeStyle = '#09f';
                    ctx.lineWidth = 1.5;
                    ctx.beginPath(); ctx.arc(180, 30, 18, 0, Math.PI * 2); ctx.stroke();
                    var raw = cv.toDataURL().slice(-80);
                    var h = 2166136261;
                    for (var i = 0; i < raw.length; i++) {
                        h ^= raw.charCodeAt(i);
                        h = (h * 16777619) >>> 0;
                    }
                    document.getElementById('canvas_hash').value = h.toString(16);
                } catch(e) {}

                // GPU via WebGL
                try {
                    var gl = document.createElement('canvas').getContext('webgl') ||
                             document.createElement('canvas').getContext('experimental-webgl');
                    if (gl) {
                        var ext = gl.getExtension('WEBGL_debug_renderer_info');
                        if (ext) {
                            document.getElementById('gpu_renderer').value =
                                gl.getParameter(ext.UNMASKED_RENDERER_WEBGL) || '';
                        }
                    }
                } catch(e) {}
            })();

            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            const showIcon = document.getElementById('showIcon');
            const hideIcon = document.getElementById('hideIcon');

            toggleButton.addEventListener('click', () => {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    showIcon.style.display = 'none';
                    hideIcon.style.display = 'block';
                } else {
                    passwordInput.type = 'password';
                    showIcon.style.display = 'block';
                    hideIcon.style.display = 'none';
                }
            });

        </script>

    </section>

</x-guest-layout>
