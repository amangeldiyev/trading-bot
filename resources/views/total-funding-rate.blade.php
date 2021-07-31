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
                                    <h1 class="text-gray-600 font-bold md:text-2xl text-xl">Total funding rate</h1>
                                </div>
                            </div>

                            <form action="" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-8 mt-5 mx-7">
                                    <div class="grid grid-cols-1">
                                        <label class="uppercase md:text-sm text-xs text-gray-500 text-light font-semibold">Symbol</label>
                                        <select name="symbol" class="py-2 px-3 rounded-lg border-2 border-purple-300 mt-1 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                            <option value="BTCUSDT" {{$symbol == 'BTCUSDT' ? 'selected' : ''}}>BTCUSDT</option>
                                            <option value="ETHUSDT" {{$symbol == 'ETHUSDT' ? 'selected' : ''}}>ETHUSDT</option>
                                            <option value="XRPUSDT" {{$symbol == 'XRPUSDT' ? 'selected' : ''}}>XRPUSDT</option>
                                            <option value="BNBUSDT" {{$symbol == 'BNBUSDT' ? 'selected' : ''}}>BNBUSDT</option>
                                            <option value="ADAUSDT" {{$symbol == 'ADAUSDT' ? 'selected' : ''}}>ADAUSDT</option>
                                        </select>
                                    </div>
                                    
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-8 mt-5 mx-7">
                                    <div class="grid grid-cols-1">
                                        <label class="uppercase md:text-sm text-xs text-gray-500 text-light font-semibold">Start date</label>
                                        <input name="start" class="py-2 px-3 rounded-lg border-2 border-purple-300 mt-1 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                            type="date" value="{{$start}}" placeholder="Start date" />
                                    </div>
                                    <div class="grid grid-cols-1">
                                        <label class="uppercase md:text-sm text-xs text-gray-500 text-light font-semibold">End date</label>
                                        <input name="end" class="py-2 px-3 rounded-lg border-2 border-purple-300 mt-1 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                            type="date" value="{{$end}}" placeholder="End date" />
                                    </div>
                                    
                                </div>


                                <div class='flex items-center justify-center  md:gap-8 gap-4 pt-5 pb-5'>
                                    <button
                                        class='w-auto bg-purple-500 hover:bg-purple-700 rounded-lg shadow-xl font-medium text-white px-4 py-2'>Calculate</button>
                                </div>
                            </form>

                            @if ($total)
                                <div class='flex items-center justify-center  md:gap-8 gap-4 pt-5 pb-5'>
                                    <p>
                                        Total funding rate for {{$symbol}}: {{$total * 100}}%
                                    </p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
