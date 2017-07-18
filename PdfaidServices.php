<?php
   class Xps2PdfConverter
   {
		public $apiKey = "";
		public $inputXpsLocation = "";
		public $outputPdfLocation = "";
		public $pdfAuthor = "";
		public $pdfTitle = "";
		public $pdfSubject = "";
		public $pdfKeywords = "";
    
			function Xps2PdfConvert()
			{ 
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputPdfLocation == "")
			  return "Please specify location to save output Pdf";
			  if($this->inputXpsLocation == "")
			  return "Please specify input XPS file Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputXpsLocation);
			  }
			 
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			   
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfTitle', $this->pdfTitle);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfAuthor', $this->pdfAuthor);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfSubject', $this->pdfSubject);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfKeywords', $this->pdfKeywords);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");	  
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->Xps2Pdf($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputPdfLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   }
   class ExtractImagesPdf
   {
		public $apiKey = "";
		public $outputImageFormat = "";
		public $inputPdfLocation = "";
		public $outputZipLocation = "";
		
			function ExtractImagesFromPdf()
			{ 
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputImageFormat == "")
			  return "Please specify Output Image Format";
			  if($this->outputZipLocation == "")
			  return "Please specify Output Zip File Location";
			  if($this->inputPdfLocation == "")
			  return "Please specify input Pdf file Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputPdfLocation);
			  }
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'outputFormat', $this->outputImageFormat);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->ExtractImagesPdf($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputZipLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   }
   class Pdf2Jpg
   {
		public $apiKey = "";
		public $outputImageFormat = "";
		public $inputPdfLocation = "";
		public $outputZipLocation = "";
		public $imageQuality = 50;
		
			function Pdf2Jpg()
			{ 
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputImageFormat == "")
			  return "Please specify Output Image Format";
			  if($this->outputZipLocation == "")
			  return "Please specify Output Zip File Location";
			  if($this->inputPdfLocation == "")
			  return "Please specify input Pdf file Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputPdfLocation);
			  }
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'outputFormat', $this->outputImageFormat);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'imageQuality', $this->imageQuality);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->Pdf2Jpg($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputZipLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   }
    class Pdf2Doc
   {
		public $apiKey = "";
		public $inputPdfLocation = "";
		public $outputDocLocation = "";
		
			function Pdf2Doc()
			{ 
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputDocLocation == "")
			  return "Please specify Output Doc File Location";
			  if($this->inputPdfLocation == "")
			  return "Please specify input Pdf file Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputPdfLocation);
			  }
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
			 $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->Pdf2Doc($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputDocLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   }
   class Svg2PdfConverter
   {
		public $apiKey = "";
		public $inputSvgLocation = "";
		public $outputPdfLocation = "";
		public $pdfAuthor = "";
		public $pdfTitle = "";
		public $pdfSubject = "";
		public $pdfKeywords = "";
		public $pdfWidth = 595;
        public $pdfHeight = 842;
		
			function Svg2PdfConvert()
			{ 
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputPdfLocation == "")
			  return "Please specify location to save output Pdf";
			  if($this->inputSvgLocation == "")
			  return "Please specify input Svg file Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputSvgLocation);
			  }
			 
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			   
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfTitle', $this->pdfTitle);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfAuthor', $this->pdfAuthor);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfSubject', $this->pdfSubject);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfKeywords', $this->pdfKeywords);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfWidth', $this->pdfWidth);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfHeight', $this->pdfHeight);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");	  
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->Svg2Pdf($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputPdfLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   }
   class Image2PdfConverter
   {
		public $apiKey = "";
		public $inputImageLocation = "";
		public $outputPdfLocation = "";
		public $imageQuality = 50;
	    public $pdfAuthor = "";
		public $pdfTitle = "";
		public $pdfSubject = "";
		public $pdfKeywords = "";
		
			function Image2PdfConvert()
			{ 
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputPdfLocation == "")
			  return "Please specify Output pdf File Location";
			  if($this->inputImageLocation == "")
			  return "Please specify input Image Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputImageLocation);
			  }
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'imageQuality', $this->imageQuality);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfTitle', $this->pdfTitle);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfAuthor', $this->pdfAuthor);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfSubject', $this->pdfSubject);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfKeywords', $this->pdfKeywords);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->Image2Pdf($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputPdfLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   }
   class Doc2PdfConverter
   {
		public $apiKey = "";
		public $inputDocLocation = "";
		public $outputPdfLocation = "";
		public $pdfAuthor = "";
		public $pdfTitle = "";
		public $pdfSubject = "";
		public $pdfKeywords = "";
		
			function Doc2PdfConvert()
			{
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputPdfLocation == "")
			  return "Please specify Output pdf File Location";
			  if($this->inputDocLocation == "")
			  return "Please specify input Doc Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputDocLocation);
			  }
			  //put extension along with pdfAuthor
			  $path_parts = pathinfo($this->inputDocLocation);
			  $this->pdfAuthor = $this->pdfAuthor + "-" + $path_parts['extension'];
			  
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfTitle', $this->pdfTitle);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfAuthor', $this->pdfAuthor);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfSubject', $this->pdfSubject);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'pdfKeywords', $this->pdfKeywords);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->Doc2Pdf($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputPdfLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   }
   class PdfCompresser
   {
		public $apiKey = "";
		public $inputPdfLocation = "";
		public $outputPdfLocation = "";
		public $compressImages = TRUE;
        public $colorImageQuality = 50;
        public $greyImageQuality = 50;
        public $monoChromeImageQuality = 50;
        public $compressStreams = TRUE;
        public $unembedComplexFonts = TRUE;
        public $unembedSimpleFonts = TRUE;
        public $unembedUnusualFonts = TRUE;
        public $flattenPdf = TRUE;
		
			function CompressPdf()
			{
			  if($this->apiKey == "")
			  return "Please specify ApiKey";
			  if($this->outputPdfLocation == "")
			  return "Please specify Output pdf File Location";
			  if($this->inputPdfLocation == "")
			  return "Please specify input Pdf Location";
			  else
			  {
				$fileStream = file_get_contents($this->inputPdfLocation);
			  }
			  
			  $parameters = array("FileByteStream" => $fileStream);
			  $wsdl = "http://apis.pdfaid.com/pdfaidservices/Service1.svc?wsdl";
			  $endpoint = "http://apis.pdfaid.com/pdfaidservices/Service1.svc";
			  $option=array('trace'=>1);
			  $client = new SoapClient($wsdl, $option);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'apikey', $this->apiKey);
              $headers[] = new SoapHeader('http://tempuri.org/', 'compressImages', $this->compressImages);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'colorImageQuality', $this->colorImageQuality);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'greyImageQuality', $this->greyImageQuality);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'monoChromeImageQuality', $this->monoChromeImageQuality);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'compressStreams', $this->compressStreams);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'unembedComplexFonts', $this->unembedComplexFonts);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'unembedSimpleFonts', $this->unembedSimpleFonts);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'unembedUnusualFonts', $this->unembedUnusualFonts);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'flattenPdf', $this->flattenPdf);
			  $headers[] = new SoapHeader('http://tempuri.org/', 'responseResult', "test");
			  $client->__setSoapHeaders($headers);
			 
			  $result = $client->CompressPdf($parameters);
			  $clientResponse = $client->__getLastResponse();
		
			  if($clientResponse == "APINOK")
			  return "API is not Valid";
			  if($clientResponse == "NOK")
			  return "Error Occured";
			  else
			  {
				$fp = fopen($this->outputPdfLocation, 'wb');
				fwrite($fp, $result->FileByteStream);
				fclose($fp);
				return "OK";
			  }
			}
   } 
?>