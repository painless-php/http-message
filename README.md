# http-message

Http messaging related functionality, implementing psr-7.

## Installation

`composer require painless-php/http-message`

## Public API

#### Psr-7 compatbile classes
* **Message** - implements MessageInterface
* **Request** - implements RequestInterface
* **Response** - implements ResponseInterface
* **Body** - implements StreamInterface
* **Uri** - implements UriInterface

#### Related http messaging classes
* BasicAuthorizationHeader
* Header
* HeaderCollection
* Query
* Status
* Method

#### Exceptions

* StringParsingException - thrown when parsing of http related content fails
