<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex justify-center">
                    <div class="flex">
                        <h1 class="text-gray-600 font-bold md:text-2xl text-xl p-2">Funding rates</h1>
                    </div>
                </div>
                <div class="p-6 bg-white border-b border-gray-200">
                    <p>BTC: <span class="font-bold text-purple-500">{{$btc_funding * 100}}%</span></p>
                    <p>ETH: <span class="font-bold text-purple-500">{{$eth_funding * 100}}%</span></p>
                    <p>&#8593; {{$highest_funding['symbol']}}: <span class="font-bold text-green-500">{{$highest_funding['lastFundingRate'] * 100}}%</span></p>
                    <p>&#8595; {{$lowest_funding['symbol']}}: <span class="font-bold text-red-500">{{$lowest_funding['lastFundingRate'] * 100}}%</span></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
