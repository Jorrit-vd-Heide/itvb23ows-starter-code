<?php 

use PHPUnit\Framework\TestCase;

class playTileTest extends TestCase {
    protected $model;
    protected $controllerMock;

    protected function setUp(): void {
        parent::setUp();
        // Arrange: Set up any necessary fixtures or dependencies
        $GLOBALS['OFFSETS'] = [[0, 1], [1, 0], [1, 1], [-1, 0], [0, -1], [-1, -1]];

        // Mock the model and view
        $this->model = $this->getMockBuilder(HiveGameModel::class)
            ->disableOriginalConstructor()
            ->getMock();
                            
        $viewMock = $this->createMock(HiveGameView::class);

        $this->controllerMock = $this->getMockBuilder(HiveGameController::class)
            ->setConstructorArgs([$this->model, $viewMock])
            ->onlyMethods(['moveTile', 'getPossibleMoves', 'playTile']) // Mock only the getPossibleMoves method
            ->getMock();
    }

    public function testIfPossibleToPlayTileOnMovedTileSpot() {
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '0,-1' => 'B', '0,2' => 'B'];

        // Set up expectations for the moveTile method
        $this->controllerMock->expects($this->once())
                            ->method('moveTile')
                            ->with('0,-1', '-1,0')
                            ->willReturn('0,-1', '-1,0');

        $this->controllerMock->moveTile('0,-1', '-1,0'); // Perform the move (move from 0,-1)

        // Set up expectations for the playTile method
        $this->controllerMock->expects($this->once())
                            ->method('playTile')
                            ->with('B', '0,3')
                            ->willReturn('B', '0,3');

        $this->controllerMock->playTile('B', '0,3'); // Perform the play

        // Set up expectations for the getPossibleMoves method
        $this->controllerMock->expects($this->once())
                             ->method('getPossibleMoves')
                             ->willReturn(['0,-1', '-1,-1', '-2,0', '-2,1', '1,-1']); // Simulate possible moves when the board is not empty

        // Act: Call the method under test
        $result = $this->controllerMock->getPossibleMoves();

        // Assert: Verify the result against the expected outcome
        $expectedMoves = ['0,-1', '-1,-1', '-2,0', '-2,1', '1,-1']; // Expected result when the board is not empty
        $this->assertEquals($expectedMoves, $result);

        // Additional assertion to check that playing a tile on the moved tile spot is allowed (check if 0,-1 is a possible play again)
        $this->assertContains('0,-1', $result);
    }
}
