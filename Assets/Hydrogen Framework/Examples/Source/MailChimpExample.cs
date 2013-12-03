﻿#region Copyright Notice & License Information
//
// MailChimpExample.cs
//
// Author:
//       Matthew Davey <matthew.davey@dotbunny.com>
//
// Copyright (c) 2013 dotBunny Inc. (http://www.dotbunny.com)
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
#endregion

using System.Collections;
using UnityEngine;

/// <summary>
/// Mail Chimp Example
/// </summary>
[AddComponentMenu ("")]
public class MailChimpExample : MonoBehaviour
{
		/// <summary>
		/// The MailChimp API key.
		/// </summary>
		/// <remarks>
		/// Found @ https://admin.mailchimp.com/account/api/
		/// </remarks>
		public string ApiKey = "";
		/// <summary>
		/// The list mini hash ID
		/// </summary>
		/// <remarks>
		/// https://admin.mailchimp.com/lists/settings/defaults?id=<somenumericid>
		/// </remarks>
		public string ListID = "";
		/// <summary>
		/// Default Email Address to use in the example
		/// </summary>
		string _emailAddress = "sample@sample.com";
		/// <summary>
		/// Storage for on screen displayed textual response
		/// </summary>
		string _response = "";
		/// <summary>
		/// The API's region
		/// </summary>
		string _apiRegion = "";

		/// <summary>
		/// Callback function, called when WebPoolWorker is finished.
		/// </summary>
		/// <param name="hash">Call Hash</param>
		/// <param name="responseHeaders">Web Response Haders</param>
		/// <param name="responseText">Web Response Payload</param>
		public void MailChimpCallback (int hash, Hashtable responseHeaders, string responseText)
		{
				// Display the hash of our call
				_response = "CALL HASH: " + hash + "\n\r\n\r";

				// Show the calls response headers
				var headers = "HEADERS\n\r========\n\r\n\r";
				foreach (var s in responseHeaders.Keys) {
						headers += s + ": " + responseHeaders [s] + "\n\r";
				}
				_response += headers;

				// Actual payload of return
				_response += "\n\rRESPONSE TEXT: \n\r==============\n\r" + responseText;
		}

		/// <summary>
		/// Unity's OnGUI Event
		/// </summary>
		public void OnGUI ()
		{
				// Display Email Address
				_emailAddress = GUI.TextField (new Rect (25, 25, 150, 25), _emailAddress);

				if (GUI.Button (new Rect (25, 60, 90, 20), "Subscribe")) {
						// Create new JSONObject
						var jsonPayload = new Hydrogen.Serialization.JSONObject ();

						// Establish API region
						_apiRegion = ApiKey.Substring (ApiKey.LastIndexOf ('-') + 1);

						// Initial setup of payload
						jsonPayload.Fields.Add ("apikey", ApiKey);
						jsonPayload.Fields.Add ("id", ListID);

						// Lazy example for a sub JSONObject; you can create JSON objects from JSON.
						jsonPayload.Fields.Add ("email", new Hydrogen.Serialization.JSONObject (
								"{\"email\":\"" + _emailAddress + "\"}"));

						// Send POST to the WebPool, telling it to callback to MailChimpCallback when finished.
						hWebPool.Instance.POST (
								"https://" + _apiRegion + ".api.mailchimp.com/2.0/lists/subscribe.json",
								"application/json",
								jsonPayload.Serialized,
								null,
								MailChimpCallback);
				}

				// Show Response
				GUI.Label (new Rect (25, 90, 500, 500), _response);
		}
}
