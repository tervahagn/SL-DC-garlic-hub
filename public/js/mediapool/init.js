"use strict";

import { DirectoryView } from "./treeview/DirectoryView.js";
import { TreeViewElements } from "./treeview/TreeViewElements.js";
import { TreeViewDialog } from "./treeview/TreeViewDialog.js";
import { TreeViewService } from "./treeview/TreeViewService.js";

import { UploaderDialog } from "./uploads/UploaderDialog.js";
import { LocalFilesPreviews } from "./uploads/Local/LocalFilesPreviews.js";
import { DragDropManager } from "./uploads/Local/DragDropManager.js";
import { PreviewFactory } from "./uploads/Preview/PreviewFactory.js";
import { FetchClient } from "../core/FetchClient.js";
import { MediaList } from "./media/MediaList.js";
import { MediaService } from "./media/MediaService.js";
import { ContextMenuMediaFactory } from "./media/ContextMenuMediaFactory.js";
import { MediaFactory } from "./media/MediaFactory.js";
import { MediaEditDialog } from "./media/MediaEditDialog.js";
import { MediaInfoDialog } from "./media/MediaInfoDialog.js";

import { UploadDialogElements } from "./uploads/UploadDialogElements.js";
import { LocalFilesElements } from "./uploads/Local/LocalFilesElements.js";
import { LocalFilesUploader } from "./uploads/Local/LocalFilesUploader.js";

import { ExternalFileUploader } from "./uploads/External/ExternalFileUploader.js";
import { ExternalFileElements } from "./uploads/External/ExternalFileElements.js";

import { SpicyCam } from "../external/spicy-camcast.min.js";
import { WebcamElements } from "./uploads/Webcam/WebcamElements.js";
import { WebcamUploader } from "./uploads/Webcam/WebcamUploader.js";
import { WebcamPreviews } from "./uploads/Webcam/WebcamPreviews.js";

import { SpicyCast } from "../external/spicy-camcast.min.js";
import { ScreencastElements } from "./uploads/Screencast/ScreencastElements.js";
import { ScreencastUploader } from "./uploads/Screencast/ScreencastUploader.js";
import { ScreencastPreviews } from "./uploads/Screencast/ScreencastPreviews.js";

import { StockPlatformElements } from "./uploads/StockPlatform/StockPlatformElements.js";
import { StockPlatformFactory } from "./uploads/StockPlatform/Platforms/StockPlatformFactory.js";
import { StockPlatformUploader } from "./uploads/StockPlatform/StockPlatformUploader.js";
import {ContextMenuTreeViewFactory} from "./treeview/ContextMenuTreeViewFactory.js";

document.addEventListener("DOMContentLoaded", function()
{
	const fetchClient     = new FetchClient();
	const mediaService    = new MediaService(fetchClient);

	const mediaList     = new MediaList(
		document.getElementById("mediaList"),
		new MediaFactory(document.getElementById('media-template')),
        new ContextMenuMediaFactory(
			document.getElementById('media-contextmenu-template'),
			new MediaEditDialog(mediaService),
			new MediaInfoDialog(mediaService),
			mediaService
		),
		mediaService
	);

	const treeViewElements = new TreeViewElements();
	const treeViewService  = new TreeViewService(fetchClient)
	const directoryView    = new DirectoryView(
		treeViewElements,
		mediaList,
		treeViewService
	);

	const treeDialog = new TreeViewDialog(
		treeViewElements,
		treeViewService
	);

	const contextMenuTreeViewFactory = new ContextMenuTreeViewFactory(
		treeViewElements,
		treeDialog,
		treeViewService,
		lang
	);
	directoryView.addContextMenu(contextMenuTreeViewFactory);

	const addRootFolder = document.getElementById('addRootFolder');
	if (addRootFolder !== null)
	{
		addRootFolder.addEventListener('click', () => {
			treeDialog.prepareShow("add_root_folder", lang);
			treeDialog.show();
		});
	}

	/**************** uploader ******************/

	const uploaderDialog = new UploaderDialog(new UploadDialogElements(), directoryView);

	// Section for local file uploads
	const localFilesElements = new LocalFilesElements();
    const previewFactory     = new PreviewFactory();
    const filePreviews       = new LocalFilesPreviews(localFilesElements.dropzonePreview, previewFactory);
	const dragDropManager    = new DragDropManager(localFilesElements, filePreviews);
	dragDropManager.init();
	filePreviews.fileUploader = new LocalFilesUploader(
		filePreviews,
		localFilesElements,
		directoryView,
		uploaderDialog,
		fetchClient
	);

	// Section for external file uploads
    new ExternalFileUploader(
        new ExternalFileElements(),
        directoryView,
        uploaderDialog,
        fetchClient
    );

	// Section for uploads from camera / webcam
    const webcamElements = new WebcamElements();
    const webcamPreviews = new WebcamPreviews(webcamElements.previewRecordsArea, previewFactory);
	webcamPreviews.fileUploader  = new WebcamUploader(
        new SpicyCam(webcamElements.webcamVideo),
        webcamPreviews,
        webcamElements,
        directoryView,
        uploaderDialog,
        fetchClient
    );

	// Section for uploads from screencasts
	const screencastElements = new ScreencastElements();
	const screencastPreviews = new ScreencastPreviews(screencastElements.previewScreencastsArea, previewFactory);
	screencastPreviews.fileUploader  = new ScreencastUploader(
		new SpicyCast(screencastElements.screencastVideo),
		screencastPreviews,
		screencastElements,
		directoryView,
		uploaderDialog,
		fetchClient
	);

	// Section for 3rd Party StockPlatforms
	const stockPlatformElements = new StockPlatformElements();
	const stockPlatformFactory = new StockPlatformFactory(fetchClient);
	new StockPlatformUploader(
		stockPlatformFactory,
		stockPlatformElements,
		directoryView,
		uploaderDialog,
		fetchClient
	);
});
