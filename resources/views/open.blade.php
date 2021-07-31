<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <div class="bg-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">


                    <div class="flex items-center justify-center">
                        <div class="grid bg-white rounded-lg shadow-xl w-11/12 md:w-9/12 lg:w-1/2">

                            <div class="flex justify-center">
                                <div class="flex">
                                    <h1 class="text-gray-600 font-bold md:text-2xl text-xl p-2">Open position</h1>
                                </div>
                            </div>

                            <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="quantity">
                                        Quantity
                                    </label>
                                    <input name="quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="quantity" type="text" placeholder="Amount">
                                </div>
                                <div class="flex items-center justify-between">
                                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                                        Open
                                    </button>
                                </div>
                            </form>
                            <p class="m-2">Difference is: {{$difference}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
