import { LabeledZoneFactory } from './LabeledZoneFactory.js';
import { CanvasView } from './CanvasView.js';
import { ContextMenu } from './ContextMenu.js';
import { ZoneProperties } from './ZoneProperties.js';
import { ZonesModel } from './ZonesModel.js';
import { CanvasEvents } from './CanvasEvents.js';
import { AutocompleteFactory } from '../../../core/AutocompleteFactory.js';

window.onload = async function ()
{
    let MyCanvas                = new fabric.Canvas("canvas", {stopContextMenu: true, fireRightClick: true, preserveObjectStacking: true})
    let MyLabeledZoneFactory    = new LabeledZoneFactory();
    let MyCanvasView            = new CanvasView(MyCanvas);

    const MyAutocompleteFactory = new AutocompleteFactory();
    const MyPlaylistSearch      = MyAutocompleteFactory.create(
        'zone_playlist',
        ThymianConfig.async_site + "?site=smil_playlist_search_by_name&type=master&search="
    );


    let MyContextMenu           = new ContextMenu(MyCanvasView);
    let MyZoneProperties        = new ZoneProperties(MyCanvasView, MyPlaylistSearch);
    let MyZonesModel            = new ZonesModel(MyCanvasView);
    let MyCanvasEvents          = new CanvasEvents(MyZonesModel, MyContextMenu, MyCanvasView, MyZoneProperties, MyLabeledZoneFactory);

    let playlist_id = document.getElementById("playlist_id").value;

    try
    {
        await MyZonesModel.loadFromDataBase(playlist_id);

        MyCanvasEvents.buildUI();
        MyCanvasEvents.initInteractions();
    }
    catch(error)
    {
        console.log("Error while loading zones:", error);

       // ThymianLog.logException("Error while loading zones:", error);
       // jThymian.printError("Error while loading zones:", error);
    }

};