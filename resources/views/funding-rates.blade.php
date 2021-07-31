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
                    <p>BTC funding: {{$btc_funding * 100}}%</p>
                    <p>ETH funding: {{$eth_funding * 100}}%</p>
                    <p>Highest funding - {{$highest_funding['symbol']}}: {{$highest_funding['value'] * 100}}%</p>
                    <p>Lowest funding - {{$lowest_funding['symbol']}}: {{$lowest_funding['value'] * 100}}%</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
