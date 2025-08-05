// apps/driver_app/lib/screens/navigation/order_navigation_screen.dart
class OrderNavigationScreen extends ConsumerStatefulWidget {
  final DeliveryOrder order;

  @override
  _OrderNavigationScreenState createState() => _OrderNavigationScreenState();
}

class _OrderNavigationScreenState extends ConsumerState<OrderNavigationScreen> {
  GoogleMapController? _mapController;
  Set<Polyline> _polylines = {};
  Set<Marker> _markers = {};
  late LatLng _currentLocation;
  StreamSubscription? _locationSubscription;

  @override
  void initState() {
    super.initState();
    _getCurrentLocation();
    _setupRoute();
  }

  Future<void> _setupRoute() async {
    // Get route from current location to destination
    final directions = await ref
        .read(directionsServiceProvider)
        .getDirections(
          origin: _currentLocation,
          destination: widget.order.currentDestination,
        );

    setState(() {
      _polylines = {
        Polyline(
          polylineId: const PolylineId('route'),
          points: directions.polylinePoints,
          color: Theme.of(context).primaryColor,
          width: 5,
        ),
      };

      _markers = {
        Marker(
          markerId: const MarkerId('current'),
          position: _currentLocation,
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueBlue),
        ),
        Marker(
          markerId: const MarkerId('destination'),
          position: widget.order.currentDestination,
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueRed),
          infoWindow: InfoWindow(
            title: widget.order.currentDestinationTitle,
            snippet: widget.order.currentDestinationAddress,
          ),
        ),
      };
    });

    // Fit map to show route
    _fitMapToRoute();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: Stack(
      children: [
        // Map
        GoogleMap(
          initialCameraPosition: CameraPosition(
            target: _currentLocation,
            zoom: 15,
          ),
          onMapCreated: (controller) => _mapController = controller,
          polylines: _polylines,
          markers: _markers,
          myLocationEnabled: true,
          myLocationButtonEnabled: false,
        ),

        // Navigation Info Card
        Positioned(
          top: MediaQuery.of(context).padding.top + 16,
          left: 16,
          right: 16,
          child: Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Row(
                    children: [
                      Icon(
                        widget.order.isPickup ? Icons.restaurant : Icons.home,
                        size: 32,
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              widget.order.currentDestinationTitle,
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            Text(
                              widget.order.currentDestinationAddress,
                              style: Theme.of(context).textTheme.bodySmall,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      Column(
                        children: [
                          const Text('Distance'),
                          Text(
                            '${widget.order.remainingDistance} km',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                        ],
                      ),
                      Column(
                        children: [
                          const Text('Time'),
                          Text(
                            '${widget.order.estimatedTime} min',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),

        // Action Button
        Positioned(
          bottom: 32,
          left: 16,
          right: 16,
          child: ElevatedButton(
            onPressed: _handleArrival,
            style: ElevatedButton.styleFrom(
              minimumSize: const Size(double.infinity, 56),
            ),
            child: Text(
              widget.order.isPickup
                  ? 'Arrived at Restaurant'
                  : 'Arrived at Customer',
            ),
          ),
        ),
      ],
    ),
  );
}
