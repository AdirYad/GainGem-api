<?php

namespace Database\Seeders;

use App\Models\RobuxGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class RobuxGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $supplier = User::where('role', User::ROLE_SUPPLIER)->first();
        $secondSupplier = User::where('role', User::ROLE_SUPPLIER)->skip(1)->first();

        if (! $supplier || ! $secondSupplier) {
            return;
        }

        RobuxGroup::create([
            'supplier_user_id' => $supplier->id,
            'cookie' => '_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_4A2E718990D4DF1C65A4D673F51D605F7F91C7E8C22712B31BE21FFE76939DE120E8B28929F0D2CEF27F8D696760FC2E2AE08128293EEC32545CDBC51502933A5B4B357893792AD3149A128D1207901615A3F02445D5B3C4519C9E2F43B652AD9500D145D4A537753F13CBC3C68202B7E6D9DBC85D188CAB66FC2045CFD12FE26F35A66CF6CBF0D004424D9A7EADEA7FB81C5C0CF8D703BBF084E3E22FD0F3948D73554F55B83442B861089762C9786F5BD0AC77D56533CAF8747133E1A68A9D970D0B8ED46A5B0B051397119F38FE0D251FC84CB42784EF767009E0BB38C23BCCCAF6699EABB50B8C0A88AA230391939C621C73508E07C7748460EF9A219CCC2E71C47F4D3D5B61E20276C4B743EC6A5E5969EB34920C5E62ED039EA23373EE22961E3E',
            'robux_group_id' => 2820850,
            'robux_owner_id' => 859953059,
            'robux_owner_username' => 'adiryed',
        ]);

        RobuxGroup::create([
            'supplier_user_id' => $secondSupplier->id,
            'cookie' => '_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_4A2E718990D4DF1C65A4D673F51D605F7F91C7E8C22712B31BE21FFE76939DE120E8B28929F0D2CEF27F8D696760FC2E2AE08128293EEC32545CDBC51502933A5B4B357893792AD3149A128D1207901615A3F02445D5B3C4519C9E2F43B652AD9500D145D4A537753F13CBC3C68202B7E6D9DBC85D188CAB66FC2045CFD12FE26F35A66CF6CBF0D004424D9A7EADEA7FB81C5C0CF8D703BBF084E3E22FD0F3948D73554F55B83442B861089762C9786F5BD0AC77D56533CAF8747133E1A68A9D970D0B8ED46A5B0B051397119F38FE0D251FC84CB42784EF767009E0BB38C23BCCCAF6699EABB50B8C0A88AA230391939C621C73508E07C7748460EF9A219CCC2E71C47F4D3D5B61E20276C4B743EC6A5E5969EB34920C5E62ED039EA23373EE22961E3E',
            'robux_group_id' => 123123,
            'robux_owner_id' => 859953059,
            'robux_owner_username' => 'adiryed',
            'created_at' => now()->addHour(),
            'updated_at' => now()->addHour(),
        ]);
    }
}
